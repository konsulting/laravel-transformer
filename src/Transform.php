<?php

namespace Konsulting\Laravel\Transformer;

class Transform
{
    /**
     * The transformer instance.
     *
     * @var Transformer
     */
    protected $transformer;

    /**
     * Indicates if the fluent API is in use.
     *
     * @var bool
     */
    protected $fluent = false;

    /**
     * The input value to be transformed.
     *
     * @var mixed
     */
    protected $data;

    /**
     * @var string
     */
    protected $currentRule;

    /**
     * @var array
     */
    protected $ruleArguments;

    /**
     * Receive Transformer instance.
     *
     * @param Transformer $transformer
     */
    public function __construct(Transformer $transformer)
    {
        $this->transformer = $transformer;
    }

    /**
     * Perform a single transformation on the input.
     *
     * @return mixed
     */
    protected function transformSingle()
    {
        $this->transform();

        return $this->data;
    }

    /**
     * Perform a transformation on the input and return $this.
     *
     * @return self
     */
    protected function transformFluent() : self
    {
        $this->transform();

        return $this;
    }

    /**
     * Perform the transformation.
     */
    protected function transform()
    {
        $this->data = $this->transformer->transform(
            ['data' => $this->data],
            ['**' => $this->constructRule()]
        )->get('data');
    }

    /**
     * Set the input to be transformed.
     *
     * @param mixed $input
     * @return self
     */
    public function input($input) : self
    {
        $this->data = $input;
        $this->fluent = true;

        return $this;
    }

    /**
     * Specify the rules to apply to the transformation.
     *
     * @param array $rules
     * @return self
     */
    public function withRules($rules) : self
    {
        if ($this->isSequentialArray($rules)) {
            foreach ($rules as $rule) {
                $this->withRule($rule);
            }

            return $this;
        }

        foreach ($rules as $rule => $arguments) {
            $this->withRule($rule, $arguments);
        }

        return $this;
    }

    /**
     * Specify a rule to apply to the transformation.
     *
     * @param string $rule
     * @param array  $arguments
     * @return self
     */
    public function withRule($rule, ...$arguments) : self
    {
        if (count($arguments) == 1 && is_array($arguments[0])) {
            $arguments = $arguments[0];
        }

        $this->currentRule = $rule;
        $this->ruleArguments = $arguments;

        return $this->transformFluent();
    }

    /**
     * Get the result of the transformation(s).
     *
     * @return mixed
     */
    public function get()
    {
        $this->fluent = false;

        return $this->data;
    }

    /**
     * Format the rule and arguments for use with the transformer.
     *
     * @return string
     */
    protected function constructRule() : string
    {
        return $this->currentRule . ($this->ruleArguments ? ':' . implode(',', $this->ruleArguments) : '');
    }

    /**
     * Allow transformer rules to be called as methods.
     *
     * @param string $method
     * @param array  $args
     * @return self|mixed
     */
    public function __call(string $method, array $args)
    {
        if ( ! $this->fluent) {
            $this->data = array_shift($args);
        }

        $this->ruleArguments = $args;
        $this->currentRule = $method;

        return $this->fluent ? $this->transformFluent() : $this->transformSingle();
    }

    /**
     * Check if an array has sequential integer keys (i.e. it is not associative).
     *
     * @param array $arr
     * @return bool
     */
    protected function isSequentialArray(array $arr)
    {
        return $arr !== [] && array_keys($arr) === range(0, count($arr) - 1);
    }

}
