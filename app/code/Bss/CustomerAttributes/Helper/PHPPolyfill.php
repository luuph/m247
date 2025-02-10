<?php

namespace Bss\CustomerAttributes\Helper;

class PHPPolyfill
{
    /**
     * Gets the first key of an array
     *
     * @param array $arr
     * @return int|string|null
     */
    function arrayKeyFirst(array $arr)
    {
        foreach ($arr as $key => $unused) {
            return $key;
        }
        return null;
    }

    /**
     * Gets the last key of an array
     *
     * @param array $array
     * @return int|string|void|null
     */
    function arrayKeyLast(array $array)
    {
        if (!is_array($array) || empty($array)) {
            return null;
        }
        return array_keys($array)[count($array)-1];
    }
}
