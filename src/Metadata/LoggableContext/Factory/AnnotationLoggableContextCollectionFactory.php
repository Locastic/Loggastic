<?php

namespace Locastic\ActivityLog\Metadata\LoggableContext\Factory;

use ApiPlatform\Core\Util\ReflectionClassRecursiveIterator;
use Locastic\ActivityLog\Annotation\Loggable;
use Locastic\ActivityLog\Metadata\LoggableContext\LoggableContextCollection;
use Doctrine\Common\Annotations\Reader;

class AnnotationLoggableContextCollectionFactory implements LoggableContextCollectionFactoryInterface
{
    private Reader $reader;
    private array $loggablePaths;
    private LoggableContextCollectionFactoryInterface $decorated;

    /**
     * @param string[] $loggablePaths
     */
    public function __construct(LoggableContextCollectionFactoryInterface $decorated, Reader $reader, array $loggablePaths)
    {
        $this->reader = $reader;
        $this->loggablePaths = $loggablePaths;
        $this->decorated = $decorated;
    }

    /**
     * {@inheritdoc}
     */
    public function create(): LoggableContextCollection
    {
        $classes = [];

        if ($this->decorated) {
            foreach ($this->decorated->create() as $loggableClass => $config) {
                $classes[$loggableClass] = $config;
            }
        }

        foreach (ReflectionClassRecursiveIterator::getReflectionClassesFromDirectories($this->loggablePaths) as $className => $reflectionClass) {
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
