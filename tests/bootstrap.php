<?php

use Konsulting\Laravel\Transformer\Transformer;
use Konsulting\Laravel\Transformer\RulePacks\CoreRulePack;

require __DIR__ . '/../vendor/autoload.php';

\Konsulting\Laravel\load_collection_extensions();

// Dummy the Laravel function for locating things in the container, for testing purposes
function app($dummy)
{
    return new Transformer(CoreRulePack::class);
}
