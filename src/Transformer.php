<?php

namespace Konsulting\Laravel\Transformer;

use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Konsulting\Laravel\Transformer\RulePacks\RulePack;
use Konsulting\Laravel\Transformer\Exceptions\InvalidRule;

class Transformer
{
    /**
     * Associative array of data to be processed.
     *
     * @var Collection
     */
    protected $data;

    /**
     * A pipe separated listing of dot-formatted data keys, used for regex matching of fields.
     *
     * @var string
     */
    protected $dataKeysForRegex = '';

    /**
     * Associative array of rules to be applied to the data.
     *
     * @var array
     */
    protected $rules = [];

    /**
     * An array of rules matched to fields, used during transform only.
     *
     * @var array
     */
    protected $matchedRules = [];

    /**
     * Flag indicating that rule processing should be halted.
     *
     * @var bool
     */
    protected $bail;

    /**
     * Flag indicating that rule processing should be halted and we should drop the current field.
     *
     * @var bool
     */
    protected $drop;

    /**
     * Index of loaded RulePacks.
     *
     * @var array
     */
    protected $rulePacks = [];

    /**
     * Index of transformation rules, linking to their parent RulePack.
     *
     * @var array
     */
    protected $ruleMethods = [];

    /**
     * Track the indices that the current field has during execution of the rules.
     *
     * @var array
     */
    protected $loopIndices = [];

    /**
     * Transformer constructor.
     *
     * @param  array|string $rulePacks
     * @param  array        $rules
     */
    public function __construct($rulePacks = [], $rules = [])
    {
        $this->addRulePacks((array) $rulePacks)->setRules($rules);
    }

    /**
     * Set the rules that will be applied to the data.
     *
     * @param array $rules
     * @return self
     */
    public function setRules(array $rules = []) : self
    {
        $this->rules = [];

        foreach ($rules as $fieldExpression => $ruleSet) {
            $this->rules[$fieldExpression] = $this->parseRuleSet($ruleSet);
        }

        return $this;
    }

    /**
     * Set the data that rules are to be applied to.
     *
     * @param array $data
     * @return self
     */
    public function setData(array $data = []) : self
    {
        $this->data = Collection::make($data)->dot();
        $this->dataKeysForRegex = $this->data->keys()->implode('|');

        return $this;
    }

    /**
     * Perform the transformation.
     *
     * @param array $data
     * @param array $rules
     * @return Collection
     */
    public function transform(array $data, array $rules = null) : Collection
    {
        $this->setData($data);

        if ($rules) {
            $this->setRules($rules);
        }

        $this->applyRules();

        return $this->data->fromDot();
    }

    /**
     * Apply the matched rules to the input.
     *
     * @return self
     */
    protected function applyRules() : self
    {
        $this->matchRulesToFields();

        foreach (array_keys($this->matchedRules) as $field) {
            $this->executeRules($field);
        }

        return $this;
    }

    /**
     * Execute the array of rules.
     *
     * @param $field
     */
    protected function executeRules($field)
    {
        $this->bail(false);
        $this->drop(false);

        foreach ($this->matchedRules[$field] as $set) {
            $this->loopIndices = $set['indices'];

            foreach ($set['set'] as $rule => $parameters) {
                $input = $this->data->fromDot($field)->first();

                if ($parameters instanceof Closure) {
                    $result = $parameters($input);
                } elseif ($parameters instanceof TransformRule) {
                    $result = $parameters->setTransformer($this)->apply($input);
                } else {
                    $result = $this->{$this->getRuleMethod($rule)}($input, ...$parameters);
                }

                if ($this->shouldDrop()) {
                    $this->data->forget($field);

                    return;
                }

                $this->replaceDataValue($field, $result);

                if ($this->shouldBail()) {
                    return;
                }
            }
        }
    }

    /**
     * Indicate that the current loop should bail.
     *
     * @param bool $bail
     */
    public function bail(bool $bail = true)
    {
        $this->bail = $bail;
    }

    /**
     * Indicate that the current field should be dropped.
     *
     * @param bool $drop
     */
    public function drop(bool $drop = true)
    {
        $this->drop = $drop;
    }

    /**
     * Construct the method name to call, given the name of the rule.
     *
     * @param $rule
     * @return string
     * @throws InvalidRule
     */
    protected function getRuleMethod($rule) : string
    {
        return 'rule' . str_replace('_', '', ucwords($rule, '_'));
    }

    /**
     * Check if the current loop should bail.
     *
     * @return bool
     */
    protected function shouldBail() : bool
    {
        return $this->bail;
    }

    /**
     * Check if the current field should be dropped.
     *
     * @return bool
     */
    protected function shouldDrop() : bool
    {
        return $this->drop;
    }

    /**
     * Match the loaded rule to fields in the data, based on the $field expression provided.
     *
     * @return self
     */
    protected function matchRulesToFields() : self
    {
        $this->matchedRules = [];

        foreach ($this->rules as $fieldExpression => $ruleSet) {
            foreach ($this->findMatchingFields($fieldExpression) as $fieldName => $indices) {
                $this->matchedRules[$fieldName][] = [
                    'fieldExpression' => $fieldExpression,
                    'indices'         => $indices,
                    'set'             => $ruleSet,
                ];
            }
        }

        return $this;
    }

    /**
     * Parse fieldExpression to match all the fields in the data we need to transform. It passes back an array of field
     * names with a set of 'indices' associated to each field name (these are where we match wildcards).
     *
     * @param $fieldExpression
     * @return array
     */
    protected function findMatchingFields($fieldExpression) : array
    {
        if ($fieldExpression == '**') {
            return array_fill_keys(explode('|', $this->dataKeysForRegex), []);
        }

        $matches = [];
        $regex = str_replace(['.', '*'], ['\.', '([^\\.|]+)'], $fieldExpression);
        preg_match_all("/({$regex})/", $this->dataKeysForRegex, $matches, PREG_SET_ORDER);

        return array_reduce($matches, function ($results, $match) {
            $results[$match[0]] = array_slice($match, 2);

            return $results;
        }, []);
    }

    /**
     * Return a key/value array of rules/parameters.
     *
     * @param $set
     * @return array
     */
    protected function parseRuleSet($set) : array
    {
        $set = is_array($set) ? $set : explode('|', $set);
        $ruleSet = [];

        foreach ($set as $expression) {
            $ruleSet = array_merge($ruleSet, $this->parseRuleExpression($expression));
        }

        return $ruleSet;
    }

    /**
     * Split a rule expression into the rule name and any parameters present.
     *
     * @param $expression
     * @return mixed
     */
    protected function parseRuleExpression($expression) : array
    {
        if ($expression instanceof Closure) {
            return [$expression->bindTo($this)];
        }

        if ($expression instanceof TransformRule) {
            return [$expression];
        }

        return $this->parseTextRuleExpression($expression);
    }

    /**
     * @param string $expression
     *
     * @return array
     */
    protected function parseTextRuleExpression(string $expression): array
    {
        $split = explode(':', $expression, 2);

        $rule = $this->validateRule($split[0]);
        $parameters = empty($split[1]) ? [] : str_getcsv($split[1]);

        return [$rule => $parameters];
    }

    /**
     * @param $rule
     *
     * @return string
     * @throws InvalidRule
     */
    protected function validateRule($rule) : string
    {
        if (! isset($this->ruleMethods[$this->getRuleMethod($rule)])) {
            throw new InvalidRule($rule);
        }

        return $rule;
    }

    /**
     * @param $name
     * @param $parameters
     * @return mixed
     */
    public function __call($name, $parameters)
    {
        if (substr($name, 0, 4) == 'rule' && $this->ruleMethods[$name]) {
            $value = array_shift($parameters);
            $rulePack = $this->rulePacks[$this->ruleMethods[$name]];

            return $rulePack->$name($value, ...$parameters);
        }
    }

    /**
     * Register multiple rule packs.
     *
     * @param array $rulePacks
     * @return Transformer
     */
    public function addRulePacks(array $rulePacks) : self
    {
        foreach ($rulePacks as $rulePack) {
            $this->addRulePack(new $rulePack);
        }

        return $this;
    }

    /**
     * Register a rule pack.
     *
     * @param RulePack|string $rulePack
     * @return Transformer
     */
    public function addRulePack($rulePack) : self
    {
        $rulePackClass = $this->getClassName($rulePack);
        $rulePack = new $rulePackClass;

        if (! ($rulePack instanceof RulePack)) {
            throw new \UnexpectedValueException('RulePack must be an instance of ' . RulePack::class);
        }

        if (! $this->hasRulePack($rulePack)) {
            $this->rulePacks[$rulePackClass] = $rulePack->transformer($this);

            $ruleMethods = array_fill_keys($rulePack->provides(), $rulePackClass);
            $this->ruleMethods = array_merge($this->ruleMethods, $ruleMethods);
        }

        return $this;
    }

    /**
     * Check if the Transformer instance has a given rule pack.
     *
     * @param RulePack|string $rulePack
     * @return bool
     */
    public function hasRulePack($rulePack) : bool
    {
        $rulePackClass = $this->getClassName($rulePack);

        return in_array($rulePackClass, array_keys($this->rulePacks));
    }

    /**
     * Return an array of all loaded rule packs.
     *
     * @return array
     */
    public function rulePacks() : array
    {
        return array_keys($this->rulePacks);
    }

    /**
     * Return class name if input is object, otherwise return input.
     *
     * @param string|object $class
     * @return string
     */
    protected function getClassName($class) : string
    {
        return is_string($class) ? $class : get_class($class);
    }

    /**
     * Parse the parameters that have been passed in and try to find an associated value in the current data
     * collection, by replacing wildcards with the indices that are kept for the current loop.
     *
     * @param $parameter
     * @return mixed
     */
    public function getValue($parameter)
    {
        return $this->data->dotGet(sprintf(str_replace('*', '%s', $parameter), ...$this->loopIndices));
    }

    /**
     * Remove the current field from the data array, and merge in the new values. For dot-delimited arrays, remove all
     * fields that are being worked on, e.g. when a date array of day, month, year is being combined into a string.
     *
     * @param string $field
     * @param mixed  $result
     */
    protected function replaceDataValue($field, $result)
    {
        $this->data = $this->data
            ->reject(function ($value, $key) use ($field) {
                return preg_match("/^{$field}$|^{$field}\./", $key);
            })
            ->merge(Arr::dot([$field => $result]));
    }
}
