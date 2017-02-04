<?php

namespace Konsulting\Laravel\Transformer;

class TransformableRequestTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    function it_returns_transformed_data()
    {
        $request = new Request(['a' => 'a']);

        $this->assertEquals(['a' => 'A'], $request->transformed()->all());
    }

    /** @test */
    function it_transforms_the_request_data_for_validation()
    {
        $request = new ExposingRequest(['a' => 'a']);

        $this->assertEquals(['a' => 'A'], $request->validationData());
    }
}

class Request {
    use TransformableRequest;

    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function all()
    {
        return $this->data;
    }

    public function transformRules()
    {
        return ['a' => 'uppercase'];
    }
}

class ExposingRequest extends Request
{
    public function validationData()
    {
        return parent::validationData();
    }
}
