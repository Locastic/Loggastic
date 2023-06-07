<?php

namespace Locastic\Loggastic\Util;

class StringConverter
{
    /**
     * Converts a word into the format for the elastic document name. Converts 'ModelName' to 'model_name'.
     */
    public static function tableize(string $word): string
    {
        $tableized = preg_replace('~(?<=\\w)([A-Z])~u', '_$1', $word);

        if (null === $tableized) {
            throw new \RuntimeException(sprintf('preg_replace returned null for value "%s"', $word));
        }

        return mb_strtolower($tableized);
    }
}
