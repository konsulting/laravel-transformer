<?php

namespace Konsulting\Transformer\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class Macros
{
    /**
     * Apply macros to the Arr class.
     *
     * @return void
     */
    public static function addArrayMacros(): void
    {
        if ( ! Arr::hasMacro('fromDot')) {
            Arr::macro('fromDot', function ($array, $separator = '.', $part = null) {
                $result = [];

                // filter dotted array before expanding
                if ($part) {
                    $array = isset($array[$part]) ? $array[$part] : $array;
                }

                if ( ! is_array($array)) {
                    return $array;
                }

                if ($part) {
                    $partPath = $part . $separator;
                    $zeroPath = '0' . $separator;

                    $array = array_filter($array, function ($item, $key) use ($partPath) {
                        return substr($key, 0, strlen($partPath)) == $partPath;
                    }, ARRAY_FILTER_USE_BOTH);

                    $array = array_reduce(array_keys($array), function ($carry, $key) use ($array, $partPath, $zeroPath) {
                        $carry[str_replace($partPath, $zeroPath, $key)] = $array[$key];

                        return $carry;
                    });
                }

                if ( ! is_array($array)) {
                    return $array;
                }

                foreach ($array as $complexKey => $val) {
                    $ref = &$result;
                    $regex = '/' . preg_quote($separator, '/') . '/';
                    $keys = preg_split($regex, $complexKey, -1, PREG_SPLIT_NO_EMPTY);

                    $finalKey = array_pop($keys);

                    foreach ($keys as $key) {
                        if ( ! isset($ref[$key])) {
                            $ref[$key] = [];
                        }
                        $ref = &$ref[$key];
                    }

                    $ref[$finalKey] = $val;
                }

                return $result;
            });
        }
    }

    /**
     * Apply macros to Collection class.
     *
     * @return void
     */
    public static function addCollectionMacros(): void
    {
        if ( ! Collection::hasMacro('dropEmpty')) {
            Collection::macro('dropEmpty', function () {
                return $this->filter(function ($value) {
                    return ! (empty($value) || $value instanceof Collection && $value->isEmpty());
                });
            });
        }

        if ( ! Collection::hasMacro('deep')) {
            Collection::macro('deep', function () {
                $arguments = func_get_args();

                $base = $this->map(function ($item) use ($arguments) {
                    if (is_array($item)) {
                        return call_user_func_array([Collection::make($item), 'deep'], $arguments)->all();
                    }

                    if ($item instanceof Collection) {
                        return call_user_func_array([$item, 'deep'], $arguments);
                    }

                    return $item;
                });

                $call = array_shift($arguments);

                return call_user_func_array([$base, $call], $arguments);
            });
        }

        if ( ! Collection::hasMacro('dotGet')) {
            Collection::macro('dotGet', function ($key) {
                return Arr::get($this, $key);
            });
        }

        if ( ! Collection::hasMacro('dotSet')) {
            Collection::macro('dotSet', function ($key, $value) {
                $data = $this->all();
                Arr::set($data, $key, $value);

                return $this->items = $data;
            });
        }

        if ( ! Collection::hasMacro('dotHas')) {
            Collection::macro('dotHas', function ($key) {
                return new static(Arr::has($this, $key));
            });
        }

        if ( ! Collection::hasMacro('dot')) {
            Collection::macro('dot', function ($prefix = '') {
                return new static(Arr::dot($this->all(), $prefix));
            });
        }

        if ( ! Collection::hasMacro('fromDot')) {
            Collection::macro('fromDot', function ($part = null) {
                return new static(Arr::fromDot($this->all(), '.', $part));
            });
        }
    }
}
