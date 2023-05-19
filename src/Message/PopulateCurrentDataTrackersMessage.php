<?php

namespace Locastic\ActivityLogs\Message;

class PopulateCurrentDataTrackersMessage
{
    private int $offset;
    private int $batchSize;
    private string $loggableClass;
    private array $loggableContext;

    public function __construct(int $offset, int $batchSize, string $loggableClass, array $loggableContext)
    {
        $this->offset = $offset;
        $this->batchSize = $batchSize;
        $this->loggableClass = $loggableClass;
        $this->loggableContext = $loggableContext;
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
