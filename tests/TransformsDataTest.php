<?php

namespace Konsulting\Transformer;

class TransformsDataTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    function test_it_applies_transform_rules_to_data()
    {
        $controller = new Controller;

        $this->assertEquals(['a' => 'A'], $controller->transform(['a' => 'a'])->toArray());
    }

    /** @test */
    function test_it_applies_passed_in_rules_to_data()
    {
        $controller = new Controller;

        $this->assertEquals(['a' => 'a', 'b' => 'B'], $controller->transform(['a' => 'a', 'b' => 'b'], ['b' => 'uppercase'])->toArray());
    }
}

class Controller {
    use TransformsData;

    public function transformRules()
    {
        return ['a' => 'uppercase'];
    }
}
