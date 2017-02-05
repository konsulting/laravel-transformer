<?php

namespace Konsulting\Laravel\Transformer;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;

class TransformerServiceProvider extends ServiceProvider
{

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/transformer.php' => config_path('transformer.php'),
        ], 'config');
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/transformer.php', 'transformer');

        $this->checkForCollectionExtensions();
        $this->registerTransformer();
    }

    /**
     * Verify we have the necessary extensions
     */
    public function checkForCollectionExtensions()
    {
        if (! Arr::hasMacro('fromDot') || ! Collection::hasMacro('fromDot')) {
            throw new \ErrorException(
                'Please register the CollectionsServiceProvider from the laravel-extend-collections package. ' .
                'Transformer requires the fromDot method for Illuminate Support Arr and Collection.'
            );
        }
    }

    /**
     * Register the Transformer singleton
     */
    public function registerTransformer()
    {
        $this->app->singleton(Transformer::class, function () {
            return (new Transformer())->addRulePacks(config('transformer.rule_packs', []));
        });
    }
}
