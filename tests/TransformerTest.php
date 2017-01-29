<?php

namespace Konsulting\Transformer;

use Carbon\Carbon;

class TransformerTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    function it_will_trim_a_string()
    {
        $transformer = new Transformer(['a' => ' '], ['a' => 'trim']);

        $this->assertEquals(['a' => ''], $transformer->go()->toArray());
    }

    /** @test */
    function it_will_apply_multiple_transformations()
    {
        $transformer = new Transformer(['a' => ' abc  '], ['a' => 'trim|uppercase']);

        $this->assertEquals(['a' => 'ABC'], $transformer->go()->toArray());
    }

    /** @test */
    function a_new_method_can_be_added()
    {
        $transformer = new Transformer(['a' => '12345'], ['a' => 'make_dots']);

        $transformer->addRuleMethod('ruleMakeDots', function ($value) {
            return str_repeat('.', strlen($value));
        });

        $this->assertEquals(['a' => '.....'], $transformer->go()->toArray());
    }

    /** @test */
    function a_new_method_can_bail()
    {
        $transformer = new Transformer(['a' => '12345 ', 'b' => ' '], ['a' => 'new_bail|trim', 'b' => 'trim']);

        $transformer->addRuleMethod('ruleNewBail', function ($value) {
            $this->bail = true;
            return $value;
        });

        $this->assertEquals(['a' => '12345 ', 'b' => ''], $transformer->go()->toArray());
    }

    /** @test */
    function an_array_of_new_methods_can_be_added()
    {
        $transformer = new Transformer(['a' => '2016-01-01'], ['a' => 'to_carbon:Y-m-d']);

        $transformer->addRuleMethod(require __DIR__ . '/../rules/konsulting_datetime.php');

        $this->assertInstanceOf(Carbon::class, $transformer->go()['a']);
    }
}
