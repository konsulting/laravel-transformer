<?php

namespace Konsulting\Laravel\Transformer;

use Konsulting\Laravel\Transformer\RulePacks\CoreRulePack;
use Konsulting\Laravel\Transformer\RulePacks\CarbonRulePack;

class TransformerTest extends \PHPUnit_Framework_TestCase
{
    protected $transformer;

    public function setUp()
    {
        parent::setUp();

        $this->transformer = (new Transformer)->addRulePack(new CoreRulePack);
    }

    /** @test */
    function it_will_trim_a_string()
    {
        $this->assertEquals(['a' => ''], $this->transformer->transform(['a' => ' '], ['a' => 'trim'])->toArray());
    }

    /** @test */
    function it_will_apply_multiple_transformations()
    {
        $this->assertEquals(['a' => 'ABC'], $this->transformer->transform(['a' => ' abc  '], ['a' => 'trim|uppercase'])->toArray());
    }

    /** @test */
    function a_new_method_can_be_added()
    {
        $this->transformer->addRuleMethod('ruleMakeDots', function ($value) {
            return str_repeat('.', strlen($value));
        });

        $this->assertEquals(['a' => '.....'], $this->transformer->transform(['a' => '12345'], ['a' => 'make_dots'])->toArray());
    }

    /** @test */
    function a_new_method_can_bail()
    {
        $this->transformer->addRuleMethod('ruleNewBail', function ($value) {
            $this->bail = true;

            return $value;
        });

        $this->assertEquals(['a' => '12345 ', 'b' => ''], $this->transformer->transform(['a' => '12345 ', 'b' => ' '], ['a' => 'new_bail|trim', 'b' => 'trim'])->toArray());
    }

    /** @test */
    function an_array_of_new_methods_can_be_added()
    {
        $ruleArray = [
            'ruleMakeDots'  => function ($value) {
                return str_repeat('.', strlen($value));
            },
            'ruleMakeStars' => function ($value) {
                return str_repeat('*', strlen($value));
            }
        ];

        $this->transformer->addRuleMethod($ruleArray);

        $this->assertEquals(
            ['a' => '........', 'b' => '*****'],
            $this->transformer->transform(['a' => '12345678', 'b' => '12345'], ['a' => 'make_dots', 'b' => 'make_stars'])->toArray()
        );
    }

    /** @test */
    function it_applies_rules_to_nested_elements()
    {
        $data = ['a' => [['name' => 'a', 'title' => 'mr'], ['name' => 'b'], ['name' => 'c']]];

        $expected = ['a' => [['name' => 'A', 'title' => 'mr'], ['name' => 'B'], ['name' => 'C']]];
        $this->assertEquals($expected, $this->transformer->transform($data, ['a.*.name' => 'uppercase'])->toArray());

        $expected = ['a' => [['name' => 'A', 'title' => 'MR'], ['name' => 'B'], ['name' => 'C']]];
        $this->assertEquals($expected, $this->transformer->transform($data, ['a.*.*' => 'uppercase'])->toArray());
    }

    /** @test */
    function it_applies_a_rule_to_all_data_at_a_single_level()
    {
        $data = ['a' => 'a', 'b' => 'b', 'c' => 'c'];
        $expected = ['a' => 'A', 'b' => 'B', 'c' => 'C'];

        $this->assertEquals($expected, $this->transformer->transform($data, ['*' => 'uppercase'])->toArray());
    }

    /** @test */
    function it_loads_rules_from_a_rule_pack_class()
    {
        $this->transformer->addRulePack(new CarbonRulePack);
    }
}
