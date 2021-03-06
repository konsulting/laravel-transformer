<?php

return [
    'rule_packs' => [
        \Konsulting\Laravel\Transformer\RulePacks\CoreRulePack::class,
        \Konsulting\Laravel\Transformer\RulePacks\CarbonRulePack::class,
    ],

    'middleware_rules' => [
        '**' => 'trim|return_null_if_empty_string',
    ],
];
