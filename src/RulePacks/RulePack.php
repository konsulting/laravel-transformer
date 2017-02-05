<?php

namespace Konsulting\Laravel\Transformer\RulePacks;

use Konsulting\Laravel\Transformer\Transformer;

abstract class RulePack
{
    protected $transformer;

    public function transformer(Transformer $transformer)
    {
        $this->transformer = $transformer;

        return $this;
    }

    public function provides()
    {
        return array_filter(get_class_methods($this), function ($method) {
            return substr($method, 0, 4) == 'rule';
        });
    }

    /*
    public function ruleExample($value)
    {
        return $value;
    }
    */
}
