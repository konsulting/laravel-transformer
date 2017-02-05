<?php

namespace Konsulting\Laravel\Transformer\RulePacks;

use Carbon\Carbon;

class CarbonRulePack extends RulePack
{
    public function ruleFormat($value, $format) {
        return $this->transformer->ruleToCarbon($value)->format($format);
    }

    public function ruleToCarbon($value, $format = null) {
        if ($value instanceof Carbon) {
            return $value;
        }

        if (isset($format)) {
            return Carbon::createFromFormat($format, $value);
        }

        return Carbon::parse($value);
    }
}
