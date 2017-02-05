<?php

namespace Konsulting\Laravel\Transformer;

use Illuminate\Support\Facades\Facade;

class TransformerFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Transformer::class;
    }
}
