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
//        $result = $this->transform()->regexReplace('aaa', '[a]{3}', 'bb');
//
//        $this->assertEquals('bb', $result);
    }

    public function transform()
    {
        return new Transform($this->transformer());
    }
}
