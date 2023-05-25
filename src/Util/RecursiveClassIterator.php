<?php

namespace Locastic\Loggastic\Util;

use Symfony\Component\Finder\Finder;

class RecursiveClassIterator
{
    public static function getReflectionClasses(array $paths): \Generator
    {
        $finder = Finder::create();
        $finder->files()->name('*.php');

        $files = iterator_to_array($finder->in($paths)->getIterator());

        foreach (get_declared_classes() as $className) {
            $reflectionClass = new \ReflectionClass($className);
            $sourceFile = $reflectionClass->getFileName();
            if (isset($files[$sourceFile])) {
                yield $className => $reflectionClass;
            }
        }
    }
}
