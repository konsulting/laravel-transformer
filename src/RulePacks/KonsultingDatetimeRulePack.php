<?php

namespace Konsulting\Transformer\RulePacks;

class KonsultingDatetimeRulePack extends LoadableRulePack {
    public function rules() : array
    {
        return [
            'ruleToPersistFormat' => function ($value, $type = 'datetime') {
                return $this->ruleFormat($value, DateTimeFormats::persistenceFormat($type));
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
    }
}
