<?php

namespace Locastic\Loggastic\Metadata\LoggableContext\Factory;

use Doctrine\Common\Annotations\Reader;
use Locastic\Loggastic\Annotation\Loggable;
use Locastic\Loggastic\Metadata\LoggableContext\LoggableContextCollection;
use Locastic\Loggastic\Util\RecursiveClassIterator;

/**
 * @deprecated since locastic/loggastic 1.2 and will be removed in 2.0, use AttributeLoggableContextCollectionFactory with the #[Loggable] attribute instead. Requires the abandoned doctrine/annotations package, which is no longer a dependency of this bundle.
 */
final class AnnotationLoggableContextCollectionFactory implements LoggableContextCollectionFactoryInterface
{
    /**
     * @param string[] $loggablePaths
     */
    public function __construct(private readonly LoggableContextCollectionFactoryInterface $decorated, private readonly Reader $reader, private readonly array $loggablePaths)
    {
        trigger_deprecation('locastic/loggastic', '1.2', 'The "%s" class is deprecated and will be removed in 2.0, use "%s" with the #[Loggable] attribute instead.', self::class, AttributeLoggableContextCollectionFactory::class);
    }

    public function create(): LoggableContextCollection
    {
        if (0 === count($this->loggablePaths)) {
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

            if (null !== $this->reader && $loggable = $this->reader->getClassAnnotation($reflectionClass, Loggable::class)) {
                $classes[$className] = $loggable->getGroups();
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
