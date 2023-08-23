<?php

namespace Locastic\Loggastic\DataProvider;

use Locastic\Loggastic\Model\Output\CurrentDataTrackerInterface;

interface CurrentDataTrackerProviderInterface
{
    public function getCurrentDataTrackerByClassAndId(string $className, $objectId): ?CurrentDataTrackerInterface;
}
