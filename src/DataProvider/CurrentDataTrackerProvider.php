<?php

namespace Locastic\Loggastic\DataProvider;

use Locastic\Loggastic\Model\Output\CurrentDataTrackerInterface;
use Locastic\Loggastic\Storage\CurrentDataTrackerStorageInterface;

final class CurrentDataTrackerProvider implements CurrentDataTrackerProviderInterface
{
    public function __construct(private readonly CurrentDataTrackerStorageInterface $currentDataTrackerStorage)
    {
    }

    public function getCurrentDataTrackerByClassAndId(string $className, $objectId): ?CurrentDataTrackerInterface
    {
        return $this->currentDataTrackerStorage->findByClassAndObjectId($className, $objectId);
    }
}
