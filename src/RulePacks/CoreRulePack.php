<?php

namespace Konsulting\Laravel\Transformer\RulePacks;

use Illuminate\Support\Collection;

class CoreRulePack extends RulePack
{
    /**
     * Convert empty values to null.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function ruleNullIfEmpty($value)
    {
        return empty($value) ? null : $value;
    }

    /**
     * Convert empty string values to null.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function ruleNullIfEmptyString($value)
    {
        return (is_string($value) && $value === '') ? null : $value;
    }

    /**
     * Stop processing rules if null.
     *
     * @param $value
     *
     * @return mixed
     */
    public function ruleBailIfNull($value)
    {
        $this->transformer->bail(is_null($value));

        return $value;
    }

    /**
     * Return null if empty, and stop processing rules.
     *
     * @param $value
     *
     * @return null
     */
    public function ruleReturnNullIfEmpty($value)
    {
        return $this->ruleBailIfNull($this->ruleNullIfEmpty($value));
    }

    /**
     * Return null if empty string, and stop processing rules.
     *
     * @param $value
     *
     * @return null
     */
    public function ruleReturnNullIfEmptyString($value)
    {
        return $this->ruleBailIfNull($this->ruleNullIfEmptyString($value));
    }

    /**
     * Drop field if value is null.
     *
     * @param $value
     *
     * @return null
     */
    public function ruleDropIfNull($value)
    {
        $this->transformer->drop(is_null($value));

        return $value;
    }

    /**
     * Drop field if value equates to empty.
     *
     * @param $value
     *
     * @return null
     */
    public function ruleDropIfEmpty($value)
    {
        return $this->ruleDropIfNull($this->ruleNullIfEmpty($value));
    }

    /**
     * Drop field if value equates to empty string.
     *
     * @param $value
     *
     * @return null
     */
    public function ruleDropIfEmptyString($value)
    {
        return $this->ruleDropIfNull($this->ruleNullIfEmptyString($value));
    }

    /**
     * Trim surrounding whitespace.
     *
     * @param        $value
     * @param string $trim
     *
     * @return string
     */
    public function ruleTrim($value, $trim = null)
    {
        return is_string($value)
            ? is_null($trim) ? trim($value) : trim($value, $trim)
            : $value;
    }

    /**
     * @param $value
     *
     * @return string
     */
    public function ruleUppercase($value)
    {
        return strtoupper($this->ruleString($value));
    }

    /**
     * @param $value
     *
     * @return string
     */
    public function ruleLowercase($value)
    {
        return strtolower($this->ruleString($value));
    }

    public function ruleString($value)
    {
        try {
            return is_array($value) ? str_putcsv($value) : (string) $value;
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

    public function ruleFloat($value)
    {
        return (float) preg_replace('/[^\d,.]/', '', $value);
    }

    public function ruleInteger($value)
    {
        return (int) $this->ruleFloat($value);
    }

    /**
     * Alias of ruleInteger.
     */
    public function ruleInt($value)
    {
        return $this->ruleInteger($value);
    }

    public function ruleDateTime($value, $timezone = null)
    {
        return new \DateTime($value, $timezone);
    }

    public function ruleDateTimeImmutable($value, $timezone = null)
    {
        return new \DateTimeImmutable($value, $timezone);
    }

    public function ruleRegexReplace($value, $regex = '*', $replace = '')
    {
        return preg_replace('/' . $regex . '/', $replace, $value);
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


    /*
     * Perform string replacement.
     */
    public function ruleReplace($value, $search, $replace)
    {
        return str_replace($search, $replace, $value);
    }
}
