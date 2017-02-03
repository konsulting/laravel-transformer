<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Konsulting\Transformer\Support\Macros;

class CollectionServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot() : void
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register() : void
    {
        Macros::addArrayMacros();
        Macros::addCollectionMacros();
    }
}
