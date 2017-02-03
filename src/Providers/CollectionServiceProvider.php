<?php

namespace Konsulting\Transformer\Providers;

use Illuminate\Support\ServiceProvider;
use Konsulting\Transformer\Support\Macros;

class CollectionServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register() : void
    {
        (new Macros)
            ->addArrayMacros()
            ->addCollectionMacros();
    }
}
