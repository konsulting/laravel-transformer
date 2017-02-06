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
}
