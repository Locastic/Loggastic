<?php

namespace Locastic\Loggastic\Metadata\LoggableContext\Factory;

use Locastic\Loggastic\Metadata\Extractor\LoggableExtractorInterface;
use Locastic\Loggastic\Metadata\LoggableContext\LoggableContextCollection;

final class ExtractorLoggableContextCollectionFactory implements LoggableContextCollectionFactoryInterface
{
    public function __construct(private readonly LoggableExtractorInterface $extractor, private readonly ?\Locastic\Loggastic\Metadata\LoggableContext\Factory\LoggableContextCollectionFactoryInterface $decorated = null)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function create(): LoggableContextCollection
    {
        $loggableClasses = [];
        if ($this->decorated) {
            foreach ($this->decorated->create() as $loggableClass => $config) {
                $loggableClasses[$loggableClass] = $config;
            }
        }

        foreach ($this->extractor->getLoggableResources() as $loggableClass => $config) {
            $loggableClasses[$loggableClass] = $config;
        }

        return new LoggableContextCollection($loggableClasses);
    }
}
