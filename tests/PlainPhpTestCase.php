<?php

use Konsulting\Laravel\Transformer\RulePacks\CoreRulePack;
use Konsulting\Laravel\Transformer\Transformer;

abstract class PlainPhpTestCase extends PHPUnit\Framework\TestCase
{
    public static function setUpBeforeClass(): void
    {
        \Konsulting\Laravel\load_collection_extensions();
    }

    public function transformer($packs = null)
    {
        return new Transformer(array_merge([CoreRulePack::class], (array) $packs));
    }
}
