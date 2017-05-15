<?php

namespace Konsulting\Laravel\Transformer;

class TransformFluent
{
    protected $transform;

    protected $data;

    public function __construct(Transform $transform)
    {
        $this->transform = $transform;
    }

    /**
     * Allow transformer rules to be called as methods.
     *
     * @param string $method
     * @param array  $arguments
     * @return self
     */
    public function __call(string $method, array $arguments) : self
    {
        $this->data = $this->transform->$method($this->data, ...$arguments);

        return $this;
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

        return $this;
    }

    /**
     * Get the result of the transformation(s).
     *
     * @return mixed
     */
    public function get()
    {
        return $this->data;
    }
}
