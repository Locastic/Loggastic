<?php

namespace Locastic\Loggastic\Metadata\LoggableContext\Factory;

use Locastic\Loggastic\Annotation\Loggable;
use Locastic\Loggastic\Metadata\LoggableContext\LoggableContextCollection;
use Locastic\Loggastic\Util\RecursiveClassIterator;

final class AnnotationLoggableContextCollectionFactory implements LoggableContextCollectionFactoryInterface
{
    /**
     * @param string[] $loggablePaths
     */
    public function __construct(
        private readonly LoggableContextCollectionFactoryInterface $decorated,
        private readonly array $loggablePaths
    ) {}

    public function create(): LoggableContextCollection
    {
        if (count($this->loggablePaths) === 0) {
            return new LoggableContextCollection([]);
        }

        $classes = [];

        if ($this->decorated) {
            foreach ($this->decorated->create() as $loggableClass => $config) {
                $classes[$loggableClass] = $config;
            }
        }

        foreach (RecursiveClassIterator::getReflectionClasses($this->loggablePaths) as $className => $reflectionClass) {
            if ($loggable = $this->getLoggableAttribute($reflectionClass)) {
                $classes[$className] = ['groups' => $loggable->getGroups()];
            }
        }

        return new LoggableContextCollection($classes);
    }

    private function getLoggableAttribute(\ReflectionClass $reflectionClass): ?Loggable
    {
        foreach ($reflectionClass->getAttributes() as $attribute) {
            if (is_a($attribute->getName(), Loggable::class, true)) {
                return $attribute->newInstance();
            }
        }

        return null;
    }
}