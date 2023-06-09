<?php

namespace Locastic\Loggastic\Metadata\Extractor;

abstract class AbstractLoggableExtractor implements LoggableExtractorInterface
{
    protected ?array $loggableClasses = null;

    public function __construct(private readonly array $loggablePaths)
    {
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
