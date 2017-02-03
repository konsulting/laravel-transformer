<?php

namespace Konsulting\Transformer\Providers;

use Illuminate\Support\ServiceProvider;
use Konsulting\Transformer\RulePacks\CarbonRulePack;
use Konsulting\Transformer\RulePacks\CoreRulePack;
use Konsulting\Transformer\Transformer;


class TransformerServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register() : void
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
    public function rulePacks() : array
    {
        return [
            CoreRulePack::class,
            CarbonRulePack::class,
        ];
    }
}
