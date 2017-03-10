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

    protected $fluent = false;

    protected $input;

    /**
     * The result of the transformation(s).
     *
     * @var mixed
     */
    protected $result;

    protected $currentRule;
    
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
     * @return self
     */
    protected function transformSingle()
    {
        return $this->transform();
    }

    /**
     * Perform a transformation on the input and return $this.
     *
     * @return $this
     */
    protected function transformFluent()
    {
        $this->result = $this->transform();

        return $this;
    }

    protected function transform()
    {
        return $this->transformer->transform(
            compact('input'),
            ['**' => $this->constructRule()]
        )->get('input');
    }

    public function input($input)
    {
        $this->input = $input;
        $this->fluent = true;

        return $this;
    }

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
     * @return mixed
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
