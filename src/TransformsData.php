<?php

namespace Konsulting\Transformer;

trait TransformsData
{
    /**
     * @param \Illuminate\Support\Collection|array $data
     * @param array                                $rules
     * @return Collection
     */
    protected function transform($data, $rules = null)
    {
        return Transformer::transform($data, is_null($rules) ? $this->transformRules() : $rules);
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
