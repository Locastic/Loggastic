<?php

namespace Locastic\Loggastic\DataProvider;

use Locastic\Loggastic\Model\CurrentDataTrackerInterface;

interface CurrentDataTrackerProviderInterface
{
    public function getCurrentDataTrackerByClassAndId(string $className, $objectId): ?CurrentDataTrackerInterface;
}
