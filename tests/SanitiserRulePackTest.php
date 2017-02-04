<?php

namespace Konsulting\Transformer;

use Konsulting\Transformer\RulePacks\SanitiserRulePack;

class SanitiserRulePackTest extends \PHPUnit_Framework_TestCase
{
    protected $transformer;

    function setUp()
    {
        $this->transformer = app(Transformer::class)
            ->addRulePack(new SanitiserRulePack);
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
