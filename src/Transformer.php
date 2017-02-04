<?php

namespace Konsulting\Transformer;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Konsulting\Transformer\Exceptions\InvalidRule;
use Konsulting\Transformer\Exceptions\UnexpectedValue;
use Konsulting\Transformer\RulePacks\LoadableRulePack;

/**
 * Class Transformer
 *
 * @package Konsulting\Transformer
 */
class Transformer
{
    /**
     * Associative array of data to be processed.
     *
     * @var array
     */
    protected $data = [];
    /**
     * Associative array of rules to be applied to the data.
     *
     * @var array
     */
    protected $rules = [];

    /**
     * An array of parsed rules.
     *
     * @var array
     */
    protected $parsedRules = [];
    /**
     * @var array
     */
    protected $dataKeysForRegex = [];

    /**
     * Flag indicating that rule processing should be halted.
     *
     * @var bool
     */
    protected $bail;
    /**
     * @var array
     */
    protected $ruleMethods = [];

    protected $loadedRulePacks = [];

    /**
     * Transformer constructor.
     *
     * @param array $data
     * @param array $rules
     */
    public function __construct(array $data = [], array $rules = [])
    {
        $this->setData($data)->setRules($rules);
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

        $this->parseRules()->applyRules();

        return $this->data->fromDot();
    }

    /**
     * Set the data that rules are to be applied to.
     *
     * @param array $data
     *
     * @return self
     */
    public function setData(array $data = []): self
    {
        $this->data = Collection::make($data)->dot();
        $this->dataKeysForRegex = $this->data->keys()->implode('|');

        return $this;
    }

    /**
     * Set the rules that will be applied to the data.
     *
     * @param array $rules
     *
     * @return self
     */
    public function setRules(array $rules = []): self
    {
        $this->rules = $rules;

        return $this;
    }

    /**
     * Parse rules given an array of rules [field1 => [rule1|rule2, field2 => [rule1|rule2]]
     *
     * @return self
     */
    protected function parseRules(): self
    {
        foreach ($this->rules as $fieldExpression => $combination) {
            $fields = $this->findMatchingFields($fieldExpression);

            $this->parsedRules = array_merge($this->parsedRules, array_fill_keys(
                $fields,
                $this->parseRuleCombination($combination)
            ));
        }

        return $this;
    }

    /**
     * Return a key/value array of rules/parameters.
     *
     * @param $combination
     *
     * @return array
     */
    protected function parseRuleCombination($combination): array
    {
        $ruleSet = [];

        foreach (explode('|', $combination) as $expression) {
            $ruleSet = array_merge($ruleSet, $this->parseRuleExpression($expression));
        }

        return $ruleSet;
    }

    /**
     * @param $expression
     *
     * @return mixed
     * @throws \Konsulting\Transformer\Exceptions\UnexpectedValue*
     */
    protected function parseRuleExpression($expression)
    {
        $split = [];

        if ( ! preg_match('/^([\w]+):?([\w-,]*)?$/', $expression, $split)) {
            throw new UnexpectedValue('Transform rules not in recognised format rule:param1,param2');
        }

        $rule = $this->validateRule($split[1]);
        $parameters = empty($split[2]) ? [] : explode(',', $split[2]);

        return [$rule => $parameters];
    }

    /**
     * Apply the parsed rules to the input
     *
     * @return self
     * @throws \Konsulting\Transformer\Exceptions\InvalidFieldException
     */
    protected function applyRules(): self
    {
        foreach (array_keys($this->parsedRules) as $field) {
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
        foreach ($this->parsedRules[$field] as $rule => $parameters) {
            $ruleMethod = $this->getRuleMethod($rule);

            $result = $this->{$ruleMethod}($this->data->fromDot($field)->first(), ...$parameters);

            $this->data = $this->data->merge(Arr::dot(is_array($result) ? $result : [$field => $result]));

            if ($this->bail) {
                return;
            }
        }
    }

    /**
     * @param $name
     * @param $parameters
     * @return mixed
     */
    public function __call($name, $parameters)
    {
        if ($this->ruleMethods[$name]) {
            $value = array_shift($parameters);

            return $this->ruleMethods[$name]($value, ...$parameters);
        }
    }

    /**
     * Checks if the given rule exists.
     *
     * @param $rule
     *
     * @return mixed
     * @throws \Konsulting\Transformer\Exceptions\InvalidRule
     */
    protected function validateRule($rule)
    {
        $ruleMethod = $this->getRuleMethod($rule);

        if ( ! method_exists($this, $ruleMethod)
            && ! isset($this->ruleMethods[$ruleMethod])
        ) {
            throw new InvalidRule($rule);
        }

        return $rule;
    }

    /**
     * @param $rule
     * @return string
     */
    protected function getRuleMethod($rule)
    {
        return 'rule' . str_replace('_', '', ucwords($rule, '_'));
    }

    /**
     * Parse fieldExpression to match all the fields in the data we need to transform
     *
     * @param $fieldExpression
     *
     * @return array
     */
    protected function findMatchingFields($fieldExpression): array
    {
        $matches = [];
        $regex = str_replace(['.', '*'], ['\.', '[^\\.|]+'], $fieldExpression);
        preg_match_all("/({$regex})/", $this->dataKeysForRegex, $matches);

        return array_unique($matches[0]);
    }


    /**
     * @param      $key
     * @param null $closure
     * @return self
     */
    public function addRuleMethod($key, $closure = null): self
    {
        if ( ! is_array($key)) {
            $this->ruleMethods[$key] = \Closure::bind($closure, $this, $this);

            return $this;
        }

        $rules = $key;
        foreach ($rules as $key => $closure) {
            $this->addRuleMethod($key, $closure);
        }

        return $this;
    }

    /**
     * @param LoadableRulePack $rulePack
     * @return self
     */
    public function addRulePack(LoadableRulePack $rulePack): self
    {
        $this->loadedRulePacks[] = get_class($rulePack);

        return $rulePack->loadTo($this);
    }

    /**
     * @param array $rulePacks
     * @return self
     */
    public function addRulePacks(array $rulePacks): self
    {
        foreach ($rulePacks as $rulePack) {
            $this->addRulePack(new $rulePack);
        }

        return $this;
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
