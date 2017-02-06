<?php

namespace Konsulting\Laravel\Transformer;

use Konsulting\Laravel\Transformer\RulePacks\CoreRulePack;

class CoreRulePackTest extends \PlainPhpTestCase
{
    protected $transformer;

    function setUp()
    {
        $this->transformer = new Transformer(CoreRulePack::class);
    }

    /** @test */
    function the_alpha_rule_removes_non_alphabetic_characters()
    {
        $data = ['a' => 'One 2 thr$ee 4-FIVE'];
        $expected = ['a' => 'One  three FIVE'];

        $this->assertEquals($expected, $this->transformer->transform($data, ['a' => 'alpha'])->toArray());
    }

    /** @test */
    function the_alpha_dash_rule_removes_non_alphabetic_and_non_dash_characters()
    {
        $data = ['a' => 'One 2 thr$ee 4-FIVE'];
        $expected = ['a' => 'One  three -FIVE'];

        $this->assertEquals($expected, $this->transformer->transform($data, ['a' => 'alphaDash'])->toArray());
    }

    /** @test **/
    public function the_null_if_empty_string_works()
    {
        $this->assertEquals(['a' => null], $this->transformer->transform(['a' => ''], ['a' => 'null_if_empty_string'])->toArray());
    }

    /** @test **/
    public function the_return_null_if_empty_string_works()
    {
        $this->assertEquals(['a' => null, 'b' => 'A'], $this->transformer->transform(['a' => '', 'b' => 'a'], ['*' => 'return_null_if_empty_string|uppercase'])->toArray());
    }

    /** @test **/
    public function the_drop_null_if_empty_string_works()
    {
        $this->assertEquals([], $this->transformer->transform(['a' => ''], ['a' => '_drop_if_empty_string'])->toArray());
    }
}
