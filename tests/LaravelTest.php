<?php

namespace Konsulting\Laravel\Transformer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Konsulting\Laravel\Transformer\RulePacks\CoreRulePack;

class LaravelTest extends \LaravelTestCase {

    /** @test */
    function the_transformer_is_available_through_the_facade()
    {
        $this->assertTrue(\Transformer::hasRulePack(CoreRulePack::class));
    }

    /** @test */
    function a_request_can_transform()
    {
        $request = Request::create('', 'POST', ['description' => '   abcdef   ']);

        $this->assertEquals(
            ['description' => 'ABCDEF'],
            $request->transform(['*' => 'trim|uppercase'])->all()
        );

        $request = Request::create('', 'GET', ['description' => '   abcdef   ']);

        $this->assertEquals(
            ['description' => 'ABCDEF'],
            $request->transform(['*' => 'trim|uppercase'])->all()
        );
    }

    /** @test */
    function a_form_request_will_be_transformed()
    {
        $request = TestTransformingRequest::create('', 'POST', ['description' => '   abcdef    ']);
        $request->setContainer($this->app);
        $request->validate([
            $request->transformRules()
            ]);

        $this->assertEquals(
            ['description' => 'ABCDEF'],
            $request->transform(['*' => 'trim|uppercase'])->all()
        );
    }
}

class TestTransformingRequest extends FormRequest {

    use TransformingRequest;

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [];
    }

    public function transformRules()
    {
        return ['*' => 'trim|uppercase'];
    }
}
