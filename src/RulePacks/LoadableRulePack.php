<?php

namespace Konsulting\Laravel\Transformer\RulePacks;

use Konsulting\Laravel\Transformer\Transformer;

abstract class LoadableRulePack
{
    /**
     * Load rule pack to specified instance.
     *
     * @param Transformer $transformer
     * @return Transformer
     */
    public function loadTo(Transformer $transformer) : Transformer
    {
        return $transformer->addRuleMethod(static::rules());
    }

    /**
     * Return an array of closures containing the rules to load.
     *
     * @return array
     */
    abstract public function rules(): array;
}
