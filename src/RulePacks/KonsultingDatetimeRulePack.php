<?php

namespace Konsulting\Laravel\Transformer\RulePacks;

class KonsultingDatetimeRulePack extends RulePack
{
    public function ruleToPersistFormat($value, $type = 'datetime')
    {
        return $this->transformer->ruleFormat($value, DateTimeFormats::persistenceFormat($type));
    }

    public function ruleToDisplayFormat($value, $type = 'datetime')
    {
        return $this->transformer->ruleFormat($value, DateTimeFormats::displayFormat($type));
    }

    public function ruleFromDisplayFormat($value, $type = 'datetime')
    {
        return $this->transformer->ruleToCarbon($value, DateTimeFormats::displayFormat($type));
    }

    public function ruleCombine($value, $type = 'datetime')
    {
        return DateTimeFormats::combine($value, DateTimeFormats::persistenceFormat($type));
    }
}
