<?php

namespace Locastic\ActivityLog\Metadata\LoggableContext\Factory;

use Locastic\ActivityLog\Metadata\Extractor\LoggableExtractorInterface;
use Locastic\ActivityLog\Metadata\LoggableContext\LoggableContextCollection;

final class ExtractorLoggableContextCollectionFactory implements LoggableContextCollectionFactoryInterface
{
    private $extractor;
    private $decorated;

    public function __construct(LoggableExtractorInterface $extractor, LoggableContextCollectionFactoryInterface $decorated = null)
    {
        $this->extractor = $extractor;
        $this->decorated = $decorated;
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
