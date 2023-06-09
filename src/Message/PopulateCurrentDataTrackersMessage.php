<?php

namespace Locastic\Loggastic\Message;

class PopulateCurrentDataTrackersMessage
{
    public function __construct(private readonly int $offset, private readonly int $batchSize, private readonly string $loggableClass, private readonly array $loggableContext)
    {
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function getBatchSize(): int
    {
        return $this->batchSize;
    }

    public function getLoggableClass(): string
    {
        return $this->loggableClass;
    }

    public function getLoggableContext(): array
    {
        return $this->loggableContext;
    }
}
