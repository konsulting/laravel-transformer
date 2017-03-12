<?php

namespace Konsulting\Laravel\Transformer;

class TransformTest extends \PlainPhpTestCase
{
    /** @test */
    function it_transforms_a_value_with_a_single_rule() {
        $result = $this->transform()->trim('  test     ');

        $this->assertEquals('test', $result);
    }

    /** @test */
    function it_may_receive_parameters_with_the_rule() {
        $result = $this->transform()->regexReplace('aaa', 'a{3}', 'bb');

        $this->assertEquals('bb', $result);
    }

    /** @test */
    function the_input_may_be_set_and_returned_with_input_and_get() {
        $result = $this->transform()->input('test')->get();

        $this->assertEquals('test', $result);
    }

    /** @test */
    function rules_may_be_applied_through_a_fluent_api() {
        $result = $this->transform()->input('  test      ')
            ->trim()
            ->uppercase()
            ->get();

        $this->assertEquals('TEST', $result);
    }

    /** @test */
    function rules_may_receive_arguments_through_the_fluent_api() {
        $result = $this->transform()->input('  test      ')
            ->regexReplace('e', 'o')
            ->trim()
            ->get();

        $this->assertEquals('tost', $result);
    }

    public function transform()
    {
        return new Transform($this->transformer());
    }
}
