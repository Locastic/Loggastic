<?php

namespace Locastic\Loggastic\Metadata\Extractor;

interface LoggableExtractorInterface
{
    public function getLoggableResources(): array;
}
