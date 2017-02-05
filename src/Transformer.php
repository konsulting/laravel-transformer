<?php

namespace Konsulting\Laravel\Transformer;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Konsulting\Laravel\Transformer\RulePacks\RulePack;
use Konsulting\Laravel\Transformer\Exceptions\InvalidRule;
use Konsulting\Laravel\Transformer\Exceptions\UnexpectedValue;

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
     * @var String
     */
    protected $dataKeysForRegex = '';

    /**
     * Associative array of rules to be applied to the data.
     *
     * @var array
     */
    protected $rules = [];

    /**
     * An array of expanded rules by field.
     *
     * @var array
     */
    protected $expandedRules = [];

    /**
     * Flag indicating that rule processing should be halted.
     *
     * @var bool
     */
    protected $bail;

    /**
     * Index of loaded RulePacks
     *
     * @var array
     */
    protected $rulePacks = [];

    /**
     * Index of transformation rules, linking to their parent RulePack
     * @var array
     */
    protected $ruleMethods = [];

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
     *
     * @return self
     */
    public function setRules(array $rules = []) : self
    {
        foreach($rules as $fieldExpression => $ruleSet) {
            $this->rules[$fieldExpression] = $this->parseRuleSet($ruleSet);
        }

        return $this;
    }

    /**
     * Set the data that rules are to be applied to.
     *
     * @param array $data
     *
     * @return self
     */
    public function setData(array $data = []) : self
    {
        $this->data = Collection::make($data)->dot();
        $this->dataKeysForRegex = $this->data->keys()->implode('|');

        return $this;
    }

    /**
     * Perform the transformation
     *
     * @param array $data
     * @param array $rules
     *
     * @return Collection
     */
    public function transform(array $data = null, array $rules = null): Collection
    {
        if ($data) {
            $this->setData($data);
        }

        if ($rules) {
            $this->setRules($rules);
        }

        $this->applyRules();

        return $this->data->fromDot();
    }

    /**
     * Apply the expanded rules to the input
     *
     * @return self
     */
    protected function applyRules() : self
    {
        $this->expandRulesByField();

        foreach (array_keys($this->expandedRules) as $field) {
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

        foreach ($this->expandedRules[$field] as $rule => $parameters) {
            $ruleMethod = $this->getRuleMethod($rule);

            $result = $this->{$ruleMethod}($this->data->fromDot($field)->first(), ...$parameters);

            $this->data = $this->data->merge(Arr::dot(is_array($result) ? $result : [$field => $result]));

            if ($this->shouldBail()) {
                return;
            }
        }
    }

    public function bail(bool $bail = true)
    {
        $this->bail = $bail;
    }

    /**
     * @param $rule
     * @return string
     * @throws InvalidRule
     */
    protected function getRuleMethod($rule) : string
    {
        return 'rule' . str_replace('_', '', ucwords($rule, '_'));
    }

    protected function shouldBail() : bool
    {
        return $this->bail;
    }

    /**
     * Parse rules given an array of rules [field1 => [rule1|rule2, field2 => [rule1|rule2]]
     *
     * @return self
     */
    protected function expandRulesByField() : self
    {
        foreach ($this->rules as $fieldExpression => $ruleSet) {
            $this->expandedRules = array_merge_recursive($this->expandedRules, array_fill_keys(
                $this->findMatchingFields($fieldExpression),
                $ruleSet
            ));
        }

        return $this;
    }

    /**
     * Parse fieldExpression to match all the fields in the data we need to transform
     *
     * @param $fieldExpression
     *
     * @return array
     */
    protected function findMatchingFields($fieldExpression) : array
    {
        $matches = [];
        $regex = str_replace(['.', '*'], ['\.', '[^\\.|]+'], $fieldExpression);
        preg_match_all("/({$regex})/", $this->dataKeysForRegex, $matches);

        return array_unique($matches[0]);
    }

    /**
     * Return a key/value array of rules/parameters.
     *
     * @param $set
     *
     * @return array
     */
    protected function parseRuleSet($set) : array
    {
        $ruleSet = [];

        foreach (explode('|', $set) as $expression) {
            $ruleSet = array_merge($ruleSet, $this->parseRuleExpression($expression));
        }

        return $ruleSet;
    }

    /**
     * @param $expression
     *
     * @return mixed
     * @throws UnexpectedValue
     */
    protected function parseRuleExpression($expression) : array
    {
        $split = [];

        if ( ! preg_match('/^([\w]+):?([\w-,"]*)?$/', $expression, $split)) {
            throw new UnexpectedValue('Transform rules not in recognised format rule:param1,param2');
        }

        $rule = $this->validateRule($split[1]);
        $parameters = empty($split[2]) ? [] : str_getcsv($split[2]);

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
     * @param array $rulePacks
     *
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
     * @param RulePack|String $rulePack
     *
     * @return Transformer
     */
    public function addRulePack($rulePack) : self
    {
        $rulePackClass = is_string($rulePack) ? $rulePack : get_class($rulePack);
        $rulePack = is_string($rulePack) ? new $rulePack : $rulePack;

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
     * @param RulePack|String $rulePack
     *
     * @return bool
     */
    public function hasRulePack($rulePack) : bool
    {
        $rulePackClass = is_object($rulePack) ? get_class($rulePack) : $rulePack;

        return in_array($rulePackClass, array_keys($this->rulePacks));
    }

    /**
     * Return an array of all loaded rule packs.
     *
     * @return array
     */
    public function rulePacks(): array
    {
        return $this->loadedRulePacks;
    }
}
