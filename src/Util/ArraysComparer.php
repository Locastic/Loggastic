<?php

namespace Locastic\Loggastic\Util;

class ArraysComparer
{
    public static function getCompared(array $currentData, array $previousData): ?array
    {
        $previousValues = ArrayDiff::arrayDiffRecursive($previousData, $currentData);
        $currentValues = ArrayDiff::arrayDiffRecursive($currentData, $previousData);

        if (!$previousValues && !$currentValues) {
            return null;
        }

        return ['previousValues' => $previousValues, 'currentValues' => $currentValues];
    }
}
