<?php

namespace Konsulting\Transformer;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Konsulting\Transformer\Exceptions\InvalidRule;
use Konsulting\Transformer\Exceptions\UnexpectedValue;

class Transformer
{
    protected $data;
    protected $rules = [];

    protected $parsedRules = [];
    protected $dataKeysForRegex = [];

    protected $bail;
    protected $ruleMethods = [];

    public function __construct($data = [], $rules = [])
    {
        $this->setData($data);
        $this->setRules($rules);
    }

    public function setData($data)
    {
        $this->data = Collection::make($data)->dot();
        $this->dataKeysForRegex = $this->data->keys()->implode('|');
    }

    public function setRules($rules)
    {
        $this->rules = $rules;
    }

    /**
     * Parse rules given an array of rules [field1 => [rule1|rule2, field2 => [rule1|rule2]]
     *
     * @return array
     */
    protected function parseRules()
    {
        foreach ($this->rules as $fieldExpression => $combination) {
            $fields = $this->findMatchingFields($fieldExpression);

            $this->parsedRules = array_merge($this->parsedRules, array_fill_keys(
                $fields,
                $this->parseRuleCombination($combination)
            ));
        }
    }

    /**
     * Return a key/value array of rules/parameters.
     *
     * @param $combination
     *
     * @return mixed
     */
    protected function parseRuleCombination($combination)
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

        if (! preg_match('/^([\w]+):?([\w-,]*)?$/', $expression, $split)) {
            throw new UnexpectedValue('Transform rules not in recognised format rule:param1,param2');
        }

        $rule = $this->validateRule($split[1]);
        $parameters = empty($split[2]) ? [] : explode(',', $split[2]);

        return [$rule => $parameters];
    }

    /**
     * Named constructor
     *
     * @param       $data
     * @param array $rules
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function transform($data, $rules)
    {
        return (new static($data, $rules))->go();
    }

    public function go()
    {
        $this->parseRules();
        $this->applyRules();

        return $this->data->fromDot();
    }

    /**
     * Apply the parsed rules to the input
     *
     * @throws \Konsulting\Transformer\Exceptions\InvalidFieldException
     */
    protected function applyRules()
    {
        foreach (array_keys($this->parsedRules) as $field) {
            $this->executeRules($field);
        }
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

    public function __call($name, $parameters)
    {
        if ($this->ruleMethods[$name]) {
            $value = array_shift($parameters);

            return $this->ruleMethods[$name]($value, ...$parameters);
        }
    }

    /**
     * @param $rule
     *
     * @return mixed
     * @throws \Konsulting\Transformer\Exceptions\InvalidRule
     */
    protected function validateRule($rule)
    {
        $ruleMethod = $this->getRuleMethod($rule);

        if (! method_exists($this, $ruleMethod)
            && ! isset($this->ruleMethods[$ruleMethod])
        ) {
            throw new InvalidRule($rule);
        }

        return $rule;
    }

    /**
     * @param $rule
     * @return string
     * @throws InvalidRuleException
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
    protected function findMatchingFields($fieldExpression)
    {
        $matches = [];
        $regex = str_replace(['.', '*'], ['\.', '[^\\.|]*'], $fieldExpression);
        preg_match_all("/({$regex}[^\\.|]*)/", $this->dataKeysForRegex, $matches);

        return array_unique($matches[0]);
    }


    public function addRuleMethod($key, $closure = null)
    {
        if (! is_array($key)) {
            $this->ruleMethods[$key] = \Closure::bind($closure, $this, $this);

            return $this;
        }

        $rules = $key;
        foreach ($rules as $key => $closure) {
            $this->addRuleMethod($key, $closure);
        }

        return $this;
    }

    /***********************************************************
     *
     *                      RULE METHODS
     *
     ***********************************************************/

    /**
     * Return null if $value is empty.
     *
     * @param mixed $value
     * @return mixed
     */
    protected function ruleNullIfEmpty($value)
    {
        return isEmpty($value) ? null : $value;
    }

    /**
     * Set bail flag if null.
     *
     * @param $value
     * @return mixed
     */
    protected function ruleBailIfNull($value)
    {
        $this->bail = is_null($value);

        return $value;
    }

    protected function ruleReturnNullIfEmpty($value)
    {
        $value = isEmpty($value) ? null : $value;

        $this->bail = is_null($value);

        return $value;
    }

    /**
     * Trim surrounding whitespace.
     *
     * @param $value
     *
     * @return string
     */
    protected function ruleTrim($value)
    {
        if (! is_string($value)) {
            return $value;
        }

        return trim($value);
    }

    public function ruleUppercase($value)
    {
        return strtoupper($value);
    }

    public function ruleLowercase($value)
    {
        return strtolower($value);
    }
}
