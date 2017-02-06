<?php

use Orchestra\Testbench\TestCase;
use Konsulting\Laravel\CollectionsServiceProvider;
use Konsulting\Laravel\Transformer\TransformerFacade;
use Konsulting\Laravel\Transformer\TransformerServiceProvider;

abstract class LaravelTestCase extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            CollectionsServiceProvider::class,
            TransformerServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Transformer' => TransformerFacade::class,
        ];
    }
}
