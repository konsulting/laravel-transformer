<?php

namespace Konsulting\Laravel\Transformer\RulePacks;

class NumberRulePack extends RulePack
{
    /*
     * Constrain a value within two limits.
     */
    function ruleClamp($value, $min, $max)
    {
        return max($min, min($max, $value));
    }
}
