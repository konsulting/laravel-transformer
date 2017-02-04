<?php

namespace Konsulting\Transformer\RulePacks;

class SanitiserRulePack extends LoadableRulePack
{
    /**
     * Return an array of closures containing the rules to load.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'ruleAlpha' => function ($value) {
                return preg_replace('/[^\pL\pM\s]/u', '', $value);
            },

            'ruleAlphaDash' => function ($value) {
                return preg_replace('/[^\pL\pM\s-]/u', '', $value);
            },
        ];
    }
}
