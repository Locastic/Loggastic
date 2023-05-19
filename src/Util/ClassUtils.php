<?php

namespace Locastic\ActivityLogs\Util;

use Doctrine\Persistence\Proxy;

class ClassUtils
{
    public static function getClass($object): string
    {
        $className = get_class($object);

        $pos = strrpos($className, '\\'.Proxy::MARKER.'\\');

        if (false === $pos) {
            /* @psalm-var class-string<T> */
            return $className;
        }

        return substr($className, $pos + Proxy::MARKER_LENGTH + 2);
    }
}
