<?php

namespace Locastic\Loggastic\Util;

use Doctrine\Persistence\Proxy;

final class ClassUtils
{
    public static function getClass($object): string
    {
        $className = $object::class;

        $pos = strrpos($className, '\\'.Proxy::MARKER.'\\');

        if (false === $pos) {
            /* @psalm-var class-string<T> */
            return $className;
        }

        return substr($className, $pos + Proxy::MARKER_LENGTH + 2);
    }
}
