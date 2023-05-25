<?php

namespace Locastic\Loggastic\Util;

class ArrayDiff
{
    public static function arrayDiffRecursive($array1, $array2): array
    {
        $return = [];

        foreach ($array1 as $key => $value) {
            if (is_array($array2) && array_key_exists($key, $array2)) {
                if (is_array($value)) {
                    $recursiveDiff = self::arrayDiffRecursive($value, $array2[$key]);
                    if (count($recursiveDiff)) {
                        $return[$key] = $recursiveDiff;
                    }
                } else {
                    if ($value != $array2[$key]) {
                        $return[$key] = $value;
                    }
                }
            } else {
                $return[$key] = $value;
            }
        }

        return $return;
    }
}
