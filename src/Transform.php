<?php

namespace Konsulting\Laravel\Transformer;

class Transform
{
    /**
     * The transformer instance.
     *
     * @var Transformer
     */
    protected $transformer;

    /**
     * Receive Transformer instance.
     */
    public function __construct(Transformer $transformer)
    {
        $this->transformer = $transformer;
    }

    /**
     * Perform the transformation.
     *
     * @return mixed
     */
    protected function transform($data, $rule, $arguments)
    {
        return $this->transformer->transform(
            ['data' => $data],
            ['**' => $this->constructRule($rule, $arguments)]
        )->get('data');
    }

    /**
     * Return a fluent Transform instance and set the data to be transformed.
     *
     * @param  mixed  $input
     */
    public function input($input): TransformFluent
    {
        return (new TransformFluent($this))->input($input);
    }

    // Transform::withRules('data', ['trim', 'uppercase'];

    /**
     * Specify the rules to apply to the transformation.
     *
     * @param  array  $rules
     * @return mixed
     */
    public function withRules($value, $rules)
    {
        if ($this->isSequentialArray($rules)) {
            foreach ($rules as $rule) {
                $value = $this->withRule($value, $rule);
            }

            return $value;
        }

        foreach ($rules as $rule => $arguments) {
            $value = $this->withRule($value, $rule, ...$arguments);
        }

        return $value;
    }

    /**
     * Specify a rule to apply to the transformation.
     *
     * @param  string  $rule
     * @param  array  $arguments
     * @return mixed
     */
    public function withRule($data, $rule, ...$arguments)
    {
        if (count($arguments) == 1 && is_array($arguments[0])) {
            $arguments = $arguments[0];
        }

        return $this->transform($data, $rule, $arguments);
    }

    /**
     * Format the rule and arguments for use with the transformer.
     *
     * @param  string  $rule
     * @param  array  $arguments
     */
    protected function constructRule($rule, $arguments = []): string
    {
        return $rule . (! empty($arguments) ? ':' . implode(',', $arguments) : '');
    }

    /**
     * Allow transformer rules to be called as methods.
     *
     * @return mixed
     */
    public function __call(string $method, array $args)
    {
        return $this->withRule($data = array_shift($args), $method, $args);
    }

    /**
     * Check if an array has sequential integer keys (i.e. it is not associative).
     */
    protected function isSequentialArray(array $arr): bool
    {
        return $arr !== [] && array_keys($arr) === range(0, count($arr) - 1);
    }
}
