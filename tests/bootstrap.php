<?php

use Konsulting\Laravel\Transformer\Transformer;
use Konsulting\Laravel\Transformer\RulePacks\CoreRulePack;

require __DIR__ . '/../vendor/autoload.php';

require __DIR__ . '/../vendor/konsulting/laravel-extend-collections/src/arr_macros.php';
require __DIR__ . '/../vendor/konsulting/laravel-extend-collections/src/collection_macros.php';

// Dummy the Laravel function for locating things in the container, for testing purposes
function app($dummy)
{
    return new Transformer(CoreRulePack::class);
}
