<?php

namespace Locastic\ActivityLog\Factory;

use Locastic\ActivityLog\Model\ActivityLogInterface;
use Locastic\ActivityLog\Model\CurrentDataTrackerInterface;

interface ActivityLogFactoryInterface
{
    public function createActivityLog($id, string $resourceClass, string $action, array $data = []): ActivityLogInterface;
    public function createCurrentDataTracker($item, $normalizedData): CurrentDataTrackerInterface;
}
