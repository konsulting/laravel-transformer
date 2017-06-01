<?php

namespace Konsulting\Laravel\Transformer;

use Konsulting\Laravel\Transformer\RulePacks\CoreRulePack;
use Konsulting\Laravel\Transformer\RulePacks\RelatedFieldsRulePack;
use Konsulting\Laravel\Transformer\RulePacks\RulePack;

class TransformerTest extends \PlainPhpTestCase
{
    /** @test */
    function it_will_load_a_rulePack()
    {
        $transformer = (new Transformer)->addRulePack(new CoreRulePack);

        $this->assertTrue($transformer->hasRulePack(new CoreRulePack));
    }

    /** @test */
    function it_will_trim_a_string()
    {
        $this->assertEquals(
            ['a' => ''],
            $this->transformer()->transform(
                ['a' => ' '],
                ['a' => 'trim']
            )->toArray()
        );
    }

    /** @test */
    function it_will_apply_multiple_transformations()
    {
        $this->assertEquals(
            ['a' => 'ABC'],
            $this->transformer()->transform(
                ['a' => ' abc  '],
                ['a' => 'trim|uppercase']
            )->toArray()
        );
    }

    /** @test */
    function it_applies_rules_to_nested_elements()
    {
        $data = ['a' => [['name' => 'a', 'title' => 'mr'], ['name' => 'b'], ['name' => 'c']]];

        $this->assertEquals(
            ['a' => [['name' => 'A', 'title' => 'mr'], ['name' => 'B'], ['name' => 'C']]],
            $this->transformer()->transform($data, ['a.*.name' => 'uppercase'])->toArray()
        );

        $this->assertEquals(
            ['a' => [['name' => 'A', 'title' => 'MR'], ['name' => 'B'], ['name' => 'C']]],
            $this->transformer()->transform($data, ['a.*.*' => 'uppercase'])->toArray()
        );
    }

    /** @test */
    function it_applies_the_special_case_apply_to_everything_double_star_properly()
    {
        $this->assertEquals(
            ['a' => ['ABC', ['name' => 'A', 'title' => 'MR'], ['name' => 'B']], 'b' => 'ABC'],
            $this->transformer()->transform(
                ['a' => ['abc', ['name' => 'a', 'title' => 'mr'], ['name' => 'b']], 'b' => 'abc'],
                ['**' => 'uppercase']
            )->toArray()
        );
    }

    /** @test */
    function it_applies_a_rule_to_all_data_at_a_single_level()
    {
        $this->assertEquals(
            ['a' => 'A', 'b' => 'B', 'c' => 'C'],
            $this->transformer()->transform(
                ['a' => 'a', 'b' => 'b', 'c' => 'c'],
                ['*' => 'uppercase']
            )->toArray()
        );
    }

    /** @test */
    function it_will_bail_from_a_rule_set()
    {
        $this->assertEquals(
            ['a' => null, 'b' => 'STRING'],
            $this->transformer()->transform(
                ['a' => [], 'b' => 'string'],
                ['*' => 'return_null_if_empty|uppercase']
            )->toArray()
        );
    }

    /** @test */
    function it_will_drop_a_field_from_the_data()
    {
        $this->assertEquals(
            ['b' => 'STRING'],
            $this->transformer()->transform(
                ['a' => [], 'b' => 'string'],
                ['*' => 'drop_if_empty|uppercase']
            )->toArray()
        );
    }

    /** @test */
    function it_merges_rules()
    {
        $this->assertEquals(
            ['a' => 'TRIM STRING'],
            $this->transformer()->transform(
                ['a' => '     trim string     '],
                ['*' => 'trim', 'a' => 'uppercase']
            )->toArray()
        );
    }

    /** @test */
    function it_returns_an_array_of_loaded_rule_packs()
    {
        $rulePacks = [rulePackOne::class, rulePackTwo::class];

        $transformer = new Transformer($rulePacks);

        $this->assertEquals($rulePacks, $transformer->rulePacks());
    }

    /** @test */
    function it_can_retrieve_values_for_use_in_rules_simple()
    {
        $data = ['a' => 'name', 'c' => 'other'];
        $expected = ['a' => null, 'c' => 'other'];;
        $transform = ['a' => 'null_without:b'];

        $this->assertEquals($expected, $this->transformer(RelatedFieldsRulePack::class)->transform($data, $transform)->toArray());
    }

    /** @test */
    function it_can_retrieve_values_for_use_in_rules_complex()
    {
        $data = ['a' => ['b' => [['name' => 'a', 'other' => 'something'],['name' => 'b'],['name' => 'c']]]];
        $expected = ['a' => ['b' => [['name' => null, 'other' => 'something'], ['name' => null], ['name' => null]]]];;
        $transform = [
            'a.*.*.name' => 'null_without:a.*.*.nothing',
        ];

        $this->assertEquals($expected, $this->transformer(RelatedFieldsRulePack::class)->transform($data, $transform)->toArray());
    }

    /** @test */
    function it_will_allow_rule_sets_to_be_provided_as_arrays()
    {
        $data = ['a' => '  trimmed  '];
        $expected = ['a' => 'TRIMMED'];;
        $transform = [
            '*' => ['trim', 'uppercase'],
        ];

        $this->assertEquals($expected,
            $this->transformer()->transform($data, $transform)->toArray());
    }

    /** @test */
    function it_will_allow_rule_sets_to_contain_closures()
    {
        $data = ['a' => '  trimmed  '];
        $expected = ['a' => 'TRIMMED'];;
        $transform = [
            '*' => [function ($input) {
                return strtoupper(trim($input));
            }],
        ];

        $this->assertEquals($expected,
            $this->transformer()->transform($data, $transform)->toArray());
    }

    /** @test */
    function it_will_allow_rule_sets_to_contain_transform_rules()
    {
        $data = ['a' => '  trimmed  '];
        $expected = ['a' => 'TRIMMED'];;
        $transform = [
            '*' => [new TestRule()],
        ];

        $this->assertEquals($expected,
            $this->transformer()->transform($data, $transform)->toArray());
    }
}


class rulePackOne extends RulePack
{
}

class rulePackTwo extends RulePack
{
}

class TestRule extends TransformRule
{
    public function apply($input) {
        return strtoupper(trim($input));
    }
}
