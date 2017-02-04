<?php

namespace Konsulting\Laravel\Transformer\Providers;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Konsulting\Laravel\Transformer\Transformer;
use Konsulting\Laravel\CollectionServiceProvider;
use Konsulting\Laravel\Transformer\RulePacks\CoreRulePack;
use Konsulting\Laravel\Transformer\RulePacks\CarbonRulePack;

class TransformerServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     */
    public function register()
    {
        $this->registerCollectionExtensionsIfMissing();
        $this->registerTransformer();
    }

    /**
     * If the developer has not explicitly loaded the extensions from
     * the Konsulting Collection Extensions, we will load them up.
     */
    public function registerCollectionExtensionsIfMissing()
    {
        if (! Arr::hasMacro('fromDot') || ! Collection::hasMacro('fromDot')) {
            (new CollectionServiceProvider)->register();
        }
    }

    /**
     * Register the Transformer singleton
     */
    public function registerTransformer()
    {
        $this->app->singleton(Transformer::class, function ($app) : Transformer {
            return (new Transformer())
                ->addRulePacks($this->rulePacks());
        });
    }

    /**
     * The rule pack classes to load.
     *
     * @return array
     */
    public function rulePacks()
    {
        return [
            CoreRulePack::class,
            CarbonRulePack::class,
        ];
    }
}
