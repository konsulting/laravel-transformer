<?php

use Carbon\Carbon;
use Konsulting\FormBuilder;

return [
    'ruleToPersistFormat' => function ($value, $type = 'datetime') {
        return $this->ruleFormat($value, DateTimeFormats::persistenceFormat($type));
    },

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

    'ruleToDisplayFormat' => function ($value, $type = 'datetime') {
        return $this->ruleFormat($value, DateTimeFormats::displayFormat($type));
    },

    'ruleFromDisplayFormat' => function ($value, $type = 'datetime') {
        return $this->ruleToCarbon($value, DateTimeFormats::displayFormat($type));
    },

    'ruleCombine' => function ($value, $type = 'datetime') {
        return DateTimeFormats::combine($value, DateTimeFormats::persistenceFormat($type));
    },
];
