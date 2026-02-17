<?php

namespace Konsulting\Laravel\Transformer;

use Konsulting\Laravel\Transformer\RulePacks\NumberRulePack;
use PHPUnit\Framework\Attributes\Test;

class NumberRullPackTest extends \PlainPhpTestCase
{
    protected $transformer;

    function setUp(): void
    {
        $this->transformer = new Transformer(NumberRulePack::class);
    }

    #[Test]
    function the_clamp_rule_clamps_correctly()
    {
        // in range
        $this->assertEquals(['a' => 1], $this->transformer->transform(['a' => 1], ['a' => 'clamp:1,3'])->toArray());

        //below
        $this->assertEquals(['a' => 1], $this->transformer->transform(['a' => 0], ['a' => 'clamp:1,3'])->toArray());

        //above
        $this->assertEquals(['a' => 3], $this->transformer->transform(['a' => 5], ['a' => 'clamp:1,3'])->toArray());
    }
}
