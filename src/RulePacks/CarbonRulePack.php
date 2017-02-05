<?php

namespace Konsulting\Laravel\Transformer\RulePacks;

use Carbon\Carbon;

class CarbonRulePack extends RulePack
{
    public function ruleDateFormat($value, $format = 'Y-m-d H:i:s', $fromFormat = null)
    {
        return $this->ruleCarbon($value, $fromFormat)->format($format);
    }

    public function ruleCarbon($value, $format = null)
    {
        if ($value instanceof Carbon) {
            return $value;
        }

        if (isset($format)) {
            return Carbon::createFromFormat($format, $value);
        }

        return Carbon::parse($value);
    }
}
