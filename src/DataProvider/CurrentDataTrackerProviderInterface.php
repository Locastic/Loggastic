<?php

namespace Locastic\ActivityLog\DataProvider;

use Locastic\ActivityLog\Model\CurrentDataTrackerInterface;

interface CurrentDataTrackerProviderInterface
{
    public function getCurrentDataTrackerByClassAndId(string $className, $objectId): ?CurrentDataTrackerInterface;
}
