<?php

namespace Konsulting\Laravel\Transformer;

trait TransformsData
{
    /**
     * @param \Illuminate\Support\Collection|array $data
     * @param array                                $rules
     * @return Collection
     */
    public function transform($data, $rules = null)
    {
        return $this->transformer()->transform($data, is_null($rules) ? $this->transformRules() : $rules);
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

    /**
     * Get the transformer for the application. Means it can be built up in a service provider if required.
     *
     * @return \Illuminate\Foundation\Application|mixed
     */
    public function transformer()
    {
        return app(Transformer::class);
    }
}
