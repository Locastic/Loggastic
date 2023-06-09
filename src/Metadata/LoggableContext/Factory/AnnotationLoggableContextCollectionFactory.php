<?php

namespace Locastic\Loggastic\Metadata\LoggableContext\Factory;

use Doctrine\Common\Annotations\Reader;
use Locastic\Loggastic\Annotation\Loggable;
use Locastic\Loggastic\Metadata\LoggableContext\LoggableContextCollection;
use Locastic\Loggastic\Util\RecursiveClassIterator;

class AnnotationLoggableContextCollectionFactory implements LoggableContextCollectionFactoryInterface
{
    /**
     * @param string[] $loggablePaths
     */
    public function __construct(private readonly LoggableContextCollectionFactoryInterface $decorated, private readonly Reader $reader, private readonly array $loggablePaths)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function create(): LoggableContextCollection
    {
        if(count($this->loggablePaths) === 0) {
            return new LoggableContextCollection([]);
        }

        $classes = [];

        if ($this->decorated) {
            foreach ($this->decorated->create() as $loggableClass => $config) {
                $classes[$loggableClass] = $config;
            }
        }

        foreach (RecursiveClassIterator::getReflectionClasses($this->loggablePaths) as $className => $reflectionClass) {
            if (
                (\PHP_VERSION_ID >= 80000 && $reflectionClass->getAttributes(Loggable::class)) ||
                (null !== $this->reader && $loggable = $this->reader->getClassAnnotation($reflectionClass, Loggable::class))
            ) {
                if (!empty($loggable)) {
                    $classes[$className] = ['groups' => $loggable->getGroups()];
                }
            }
        }

        return new LoggableContextCollection($classes);
    }
}
