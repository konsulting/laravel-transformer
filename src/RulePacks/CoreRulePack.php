<?php

namespace Konsulting\Laravel\Transformer\RulePacks;

use Illuminate\Support\Collection;

class CoreRulePack extends RulePack
{
    /**
     * Convert empty values to null.
     *
     * @param mixed $value
     * @return mixed
     */
    public function ruleNullIfEmpty($value) {
        return empty($value) ? null : $value;
    }

    /**
     * Stop processing rules if null.
     *
     * @param $value
     * @return mixed
     */
    public function ruleBailIfNull($value) {
        $this->transformer->bail(is_null($value));

        return $value;
    }

    /**
     * Return null if empty, and stop processing rules.
     *
     * @param $value
     * @return null
     */
    public function ruleReturnNullIfEmpty($value) {
        return $this->ruleBailIfNull($this->ruleNullIfEmpty($value));
    }

    /**
     * Trim surrounding whitespace.
     *
     * @param        $value
     * @param string $trim
     *
     * @return string
     */
    public function ruleTrim($value, $trim = ' ') {
        return is_string($value) ? trim($value, $trim) : $value;
    }

    /**
     * @param $value
     * @return string
     */
    public function ruleUppercase($value) {
        return strtoupper($value);
    }

    /**
     * @param $value
     * @return string
     */
    public function ruleLowercase($value) {
        return strtolower($value);
    }

    public function ruleString($value)
    {
        try {
            return is_array($value) ? str_getcsv($value): (string) $value;
        } catch (\Exception $e) {
            return '';
        }
    }

    public function ruleBoolean($value)
    {
        return (bool) $value;
    }

    public function ruleArray($value)
    {
        return (array) $value;
    }

    public function ruleCollection($value)
    {
        return Collection::make($this->ruleArray($value));
    }

    public function ruleJson($value)
    {
        return $this->ruleCollection($value)->toJson();
    }

    public function ruleRegexReplace($value, $regex = '*', $replace = '')
    {
        return preg_replace('/' . preg_quote($regex, '/') . '/', $replace, $value);
    }

    public function ruleFloat($value)
    {
        return (float) preg_replace('/[^\d,.]/', '', $value);
    }

    public function ruleInteger($value)
    {
        return (int) $this->ruleFloat($value);
    }

    /*
     * Return only numeric characters
     */
    public function ruleNumeric($value)
    {
        return preg_replace('/[^\pN]/u', '', $value);
    }

    /*
     * Return only alphabetic characters
     */
    public function ruleAlpha($value)
    {
        return preg_replace('/[^\pL\pM\s]/u', '', $value);
    }

    /*
     * Return only alphabetic characters, underscore or dash
     */
    public function ruleAlphaDash($value)
    {
        return preg_replace('/[^\pL\pM\s_-]/u', '', $value);
    }

    /*
     * Return only alphabetic and numeric characters
     */
    public function ruleAlphaNum($value)
    {
        return preg_replace('/[^\pL\pM\pN\s]/u', '', $value);
    }

    /*
     * Return only alphabetic, numeric, underscore and dash characters
     */
    public function ruleAlphaNumDash($value)
    {
        return preg_replace('/[^\pL\pM\pN\s_-]/u', '', $value);
    }
}
