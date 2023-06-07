<?php

namespace Locastic\Loggastic\DataProcessor;

use Locastic\Loggastic\Message\CreateActivityLogMessageInterface;
use Locastic\Loggastic\Message\DeleteActivityLogMessageInterface;
use Locastic\Loggastic\Message\UpdateActivityLogMessageInterface;
use Locastic\Loggastic\Model\CurrentDataTracker;

interface ActivityLogProcessorInterface
{
    public function processCreatedItem(CreateActivityLogMessageInterface $message): void;

    public function processUpdatedItem(UpdateActivityLogMessageInterface $message, CurrentDataTracker $currentDataTracker): void;

    public function processDeletedItem(DeleteActivityLogMessageInterface $message): void;
}
