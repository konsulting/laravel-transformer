<?php

namespace Konsulting\Laravel\Transformer;

trait TransformingRequest
{
    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $this->transform($this->transformRules());
    }

    /**
     * Get the rules for the transform.
     *
     * @return array
     */
    public function transformRules()
    {
        return [];
    }
}
