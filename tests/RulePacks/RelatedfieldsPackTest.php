<?php

namespace Konsulting\Laravel\Transformer;

use Konsulting\Laravel\Transformer\RulePacks\CoreRulePack;
use Konsulting\Laravel\Transformer\RulePacks\RelatedFieldsRulePack;

class RelatedfieldsPackTest extends \PlainPhpTestCase
{
    protected $transformer;

    function setUp(): void
    {
        $this->transformer = new Transformer([CoreRulePack::class, RelatedFieldsRulePack::class]);
    }

    /** @test */
    function it_will_return_null_without_another_field()
    {
        $data = ['a' => 'name'];
        $expected = ['a' => null];

        $this->assertEquals($expected, $this->transformer->transform($data, ['a' => 'null_without:b'])->toArray());
    }

    /** @test */
    function it_will_drop_without_another_field()
    {
        $data = ['a' => 'name'];
        $expected = [];

        $this->assertEquals($expected, $this->transformer->transform($data, ['a' => 'drop_without:b'])->toArray());
    }

    /** @test */
    function it_will_bail_without_another_field()
    {
        $data = ['a' => 'name'];
        $expected = ['a' => 'name'];

        $this->assertEquals($expected, $this->transformer->transform($data, ['a' => 'bail_without:b|uppercase'])->toArray());
    }

    /** @test */
    function it_will_return_null_with_another_field()
    {
        $data = ['a' => 'name', 'b' => 'something'];
        $expected = ['a' => null, 'b' => 'something'];

        $this->assertEquals($expected, $this->transformer->transform($data, ['a' => 'null_with:b'])->toArray());
    }

    /** @test */
    function it_will_drop_with_another_field()
    {
        $data = ['a' => 'name', 'b' => 'something'];
        $expected = ['b' => 'something'];

        $this->assertEquals($expected, $this->transformer->transform($data, ['a' => 'drop_with:b'])->toArray());
    }

    /** @test */
    function it_will_bail_with_another_field()
    {
        $data = ['a' => 'name', 'b' => 'something'];
        $expected = ['a' => 'name', 'b' => 'something'];

        $this->assertEquals($expected,
            $this->transformer->transform($data, ['a' => 'bail_with:b|uppercase'])->toArray());
    }
}
