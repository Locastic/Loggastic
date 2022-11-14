<?php

namespace Locastic\ActivityLog\Metadata\Extractor;

abstract class AbstractLoggableExtractor implements LoggableExtractorInterface
{
    protected ?array $loggableClasses = null;
    private array $loggablePaths;

    public function __construct(array $loggablePaths)
    {
        $this->loggablePaths = $loggablePaths;
    }

    abstract protected function extractPath(string $path);

    public function getLoggableResources(): array
    {
        if (null !== $this->loggableClasses) {
            return $this->loggableClasses;
        }

        $this->loggableClasses = [];
        foreach ($this->loggablePaths as $path) {
            $this->extractPath($path);
        }

        return $this->loggableClasses;
    }
}
