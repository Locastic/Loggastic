<?php

namespace Locastic\Loggastic\Logger;

interface ActivityLoggerInterface
{
    public function logCreatedItem(object $item, ?string $actionName = null): void;

    public function logDeletedItem($objectId, string $className, ?string $actionName = null): void;

    public function logUpdatedItem($item, ?string $actionName = null, bool $createLogWithoutChanges = false);
}
