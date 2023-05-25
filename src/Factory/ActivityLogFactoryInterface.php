<?php

namespace Locastic\Loggastic\Factory;

use Locastic\Loggastic\Message\ActivityLogMessageInterface;
use Locastic\Loggastic\Model\ActivityLogInterface;
use Locastic\Loggastic\Model\CurrentDataTrackerInterface;

interface ActivityLogFactoryInterface
{
    public function create(): ActivityLogInterface;

    public function createFromActivityLogMessage(ActivityLogMessageInterface $activityLogMessage, ?array $data = []): ActivityLogInterface;

    public function createCurrentDataTracker($item, $normalizedData): CurrentDataTrackerInterface;
}
