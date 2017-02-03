<?php

namespace Konsulting\Transformer\RulePacks;

use Carbon\Carbon;

class CarbonRulePack extends LoadableRulePack
{
    public function rules(): array
    {
        return [
            'ruleFormat' => function ($value, $format) {
                return $this->ruleToCarbon($value)->format($format);
            },

            'ruleToCarbon' => function ($value, $format = null) {
                if ($value instanceof Carbon) {
                    return $value;
                }

                if (isset($format)) {
                    return Carbon::createFromFormat($format, $value);
                }

                return Carbon::parse($value);
            },
        ];
    }
}
