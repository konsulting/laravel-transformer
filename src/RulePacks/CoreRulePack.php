<?php

namespace Konsulting\Transformer\RulePacks;

class CoreRulePack extends LoadableRulePack
{
    /**
     * Return an array of closures containing the rules to load.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            /**
             * Return null if $value is empty.
             *
             * @param mixed $value
             * @return mixed
             */
            'ruleNullIfEmpty'       => function ($value) {
                return isEmpty($value) ? null : $value;
            },

            /**
             * Set bail flag if null.
             *
             * @param $value
             * @return mixed
             */
            'ruleBailIfNull'        => function ($value) {
                $this->bail = is_null($value);

                return $value;
            },

            /**
             * @param $value
             * @return null
             */
            'ruleReturnNullIfEmpty' => function ($value) {
                $value = isEmpty($value) ? null : $value;

                $this->bail = is_null($value);

                return $value;
            },

            /**
             * Trim surrounding whitespace.
             *
             * @param $value
             * @return string
             */
            'ruleTrim'              => function ($value) {
                if ( ! is_string($value)) {
                    return $value;
                }

                return trim($value);
            },

            /**
             * @param $value
             * @return string
             */
            'ruleUppercase'         => function ($value) {
                return strtoupper($value);
            },

            /**
             * @param $value
             * @return string
             */
            'ruleLowercase'         => function ($value) {
                return strtolower($value);
            }
        ];
    }
}
