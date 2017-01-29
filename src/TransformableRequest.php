<?php

namespace Konsulting\Transformer;

trait TransformableRequest
{
    /**
     * To cache the transformed data.
     * @var
     */
    protected $transformedData;

    /**
     * Get the transformed data and cache to prevent unnecessary processing.
     *
     * @return Collection
     */
    public function transformed()
    {
        if (! isset($this->transformedData)) {
            $this->transformedData = $this->transform($this->all());
        }

        return $this->transformedData;
    }

    /**
     * Transform the data before passing to validator.
     *
     * @return array
     */
    protected function validationData()
    {
        return $this->transformed()->all();
    }
}
