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
    protected $input;

    /**
     * The result of the transformation(s).
     *
     * @var mixed
     */
    protected $result;

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

        return $this->result;
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
        $this->result = $this->transformer->transform(
            ['input' => $this->input],
            ['**' => $this->constructRule()]
        )->get('input');
    }

    /**
     * Set the input to be transformed.
     *
     * @param mixed $input
     * @return self
     */
    public function input($input) : self
    {
        $this->input = $input;
        $this->fluent = true;

        return $this;
    }

    /**
     * Get the result of the transformation(s).
     *
     * @return mixed
     */
    public function get()
    {
        $this->fluent = false;

        return $this->result;
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
            $this->input = array_shift($args);
        }

        $this->ruleArguments = $args;
        $this->currentRule = $method;

        return $this->fluent ? $this->transformFluent() : $this->transformSingle();
    }

}
