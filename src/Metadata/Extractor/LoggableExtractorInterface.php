<?php

namespace Locastic\ActivityLog\Metadata\Extractor;

interface LoggableExtractorInterface
{
    public function getLoggableResources(): array;
}
