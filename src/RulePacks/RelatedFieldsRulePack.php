<?php

namespace Konsulting\Laravel\Transformer\RulePacks;

class RelatedFieldsRulePack extends RulePack
{
    /*
     * Make a value null if a related field is not present
     */
    public function ruleNullWithout($value, $check)
    {
        return is_null($this->transformer->getValue($check)) ? null : $value;
    }

    /*
     * Drop if a related field is not present
     */
    public function ruleDropWithout($value, $check)
    {
        $this->transformer->drop(is_null($this->transformer->getValue($check)));

        return $value;
    }

    /*
    * Drop if a related field is not present
    */
    public function ruleBailWithout($value, $check)
    {
        $this->transformer->bail(is_null($this->transformer->getValue($check)));

        return $value;
    }

    /*
     * Make a value null if a related field is present
     */
    public function ruleNullWith($value, $check)
    {
        return ! is_null($this->transformer->getValue($check)) ? null : $value;
    }

    /*
     * Drop if a related field is present
     */
    public function ruleDropWith($value, $check)
    {
        $this->transformer->drop(! is_null($this->transformer->getValue($check)));

        return $value;
    }

    /*
    * Drop if a related field is not present
    */
    public function ruleBailWith($value, $check)
    {
        $this->transformer->bail(! is_null($this->transformer->getValue($check)));

        return $value;
    }
}
