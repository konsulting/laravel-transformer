<?php

require __DIR__ . '/../vendor/autoload.php';

require __DIR__ . '/../vendor/konsulting/laravel-extend-collections/src/arr_macros.php';
require __DIR__ . '/../vendor/konsulting/laravel-extend-collections/src/collection_macros.php';

// Dummy the Laravel function for locating things in the container, for testing purposes
function app($dummy)
{
    return (new Konsulting\Transformer\Transformer)
        ->addRulePack(new \Konsulting\Transformer\RulePacks\CoreRulePack);
}
