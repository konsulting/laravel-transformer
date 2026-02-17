<?php

namespace Konsulting\Laravel\Transformer\Middleware;

use Closure;
use Illuminate\Http\Request;

class TransformRequest
{
    /**
     * Handle the request.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $request->transform(config('transformer.middleware_rules'));

        return $next($request);
    }
}
