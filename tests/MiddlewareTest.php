<?php

namespace Konsulting\Laravel\Transformer;

use Illuminate\Http\Request;
use Konsulting\Laravel\Transformer\Middleware\TransformRequest;

class MiddlewareTest extends \LaravelTestCase
{
    /** @test */
    function it_nullifies_if_empty_and_trims_request_data()
    {
        $request = Request::create('', 'POST', ['name' => '   a b c     ', 'email' => '', 'address' => '     ']);

        (new TransformRequest)->handle($request, function ($request) {
            $this->assertEquals('a b c', $request->input('name'));
            $this->assertNull($request->input('email'));
            $this->assertNull($request->input('address'));
        });
    }
}
