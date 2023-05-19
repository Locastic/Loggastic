<?php

namespace Locastic\ActivityLogs\Message;

interface UpdateActivityLogMessageInterface extends ActivityLogMessageInterface
{
    public function getUpdatedItem(): object;

    public function isCreateLogWithoutChanges(): bool;

    public function getNormalizedItem(): array;

    public function setNormalizedItem(array $normalizedItem): void;
}
