<?php

namespace Konsulting\Laravel\Transformer;

abstract class TransformRule
{
    protected $transformer;

    public function setTransformer(Transformer $transformer)
    {
        $this->transformer = $transformer;

        return $this;
    }

    abstract public function apply($input);
}
