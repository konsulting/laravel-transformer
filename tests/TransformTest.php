<?php

namespace Konsulting\Laravel\Transformer;

class TransformTest extends \PlainPhpTestCase
{
    /** @test */
    public function it_transforms_a_value_with_a_single_rule()
    {
        $result = $this->transform()->trim('  test     ');

        $this->assertEquals('test', $result);
    }

    /** @test */
    public function it_may_receive_parameters_with_the_rule()
    {
        $result = $this->transform()->regexReplace('aaa', 'a{3}', 'bb');

        $this->assertEquals('bb', $result);
    }

    /** @test */
    public function a_rule_may_be_applied_using_the_with_rule_method()
    {
        $result = $this->transform()->withRule('   test  ', 'trim');

        $this->assertEquals('test', $result);
    }

    /** @test */
    public function a_rule_and_arguments_may_be_applied_using_the_with_rule_method()
    {
        $result = $this->transform()->withRule('test', 'regex_replace', 'e', 'oa');

        $this->assertEquals('toast', $result);
    }

    /** @test */
    public function a_rule_and_arguments_as_an_array_may_be_applied_using_the_with_rule_method()
    {
        $result = $this->transform()->withRule('test', 'regex_replace', ['e', 'oa']);

        $this->assertEquals('toast', $result);
    }

    /** @test */
    public function multiple_rules_may_be_applied_using_the_with_rules_method()
    {
        $result = $this->transform()->withRules('   test  ', ['trim', 'uppercase']);

        $this->assertEquals('TEST', $result);
    }

    /** @test */
    public function multiple_rules_and_arguments_may_be_applied_using_the_with_rules_method()
    {
        $result = $this->transform()->withRules('--test---', ['trim' => ['-'], 'regex_replace' => ['e', 'oa']]);

        $this->assertEquals('toast', $result);
    }

    /** @test */
    public function the_input_may_be_set_and_returned_with_input_and_get()
    {
        $result = $this->transform()->input('test')->get();

        $this->assertEquals('test', $result);
    }

    /** @test */
    public function rules_may_be_applied_through_a_fluent_api()
    {
        $result = $this->transform()->input('  test      ')
            ->trim()
            ->uppercase()
            ->get();

        $this->assertEquals('TEST', $result);
    }

    /** @test */
    public function rules_may_receive_arguments_through_the_fluent_api()
    {
        $result = $this->transform()->input('  test      ')
            ->regexReplace('e', 'oa')
            ->trim()
            ->get();

        $this->assertEquals('toast', $result);
    }

    /** @test */
    public function a_rule_can_be_specified_through_the_with_rule_method()
    {
        $result = $this->transform()->input('  test  ')->withRule('trim')->get();

        $this->assertEquals('test', $result);
    }

    /** @test */
    public function a_rule_and_arguments_can_be_specified_through_the_with_rule_method()
    {
        $result = $this->transform()->input('test')->withRule('regex_replace', 'e', 'oa')->get();

        $this->assertEquals('toast', $result);
    }

    /** @test */
    public function a_rule_and_arguments_as_an_array_can_be_specified_through_the_with_rule_method()
    {
        $result = $this->transform()->input('test')->withRule('regex_replace', ['e', 'oa'])->get();

        $this->assertEquals('toast', $result);
    }

    /** @test */
    public function multiple_rules_can_be_specified_through_the_with_rules_method()
    {
        $rules = ['trim', 'uppercase'];
        $result = $this->transform()->input('   test   ')->withRules($rules)->get();

        $this->assertEquals('TEST', $result);
    }

    /** @test */
    public function multiple_rules_and_arguments_can_be_specified_through_the_with_rules_method()
    {
        $rules = [
            'regex_replace' => ['e', 'oa'],
            'uppercase'     => []
        ];
        $result = $this->transform()->input('test')->withRules($rules)->get();

        $this->assertEquals('TOAST', $result);
    }

    protected function transform()
    {
        return new Transform($this->transformer());
    }
}
