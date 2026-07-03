<?php

namespace Locastic\Loggastic\DataProvider;

use Locastic\Loggastic\Storage\ActivityLogStorageInterface;

final class ActivityLogProvider implements ActivityLogProviderInterface
{
    public function __construct(private readonly ActivityLogStorageInterface $activityLogStorage)
    {
    }

    public function getActivityLogsByClass(string $className, array $sort = [], int $limit = 20, int $offset = 0): array
    {
        return $this->activityLogStorage->findByClass($className, $sort, $limit, $offset);
    }

    public function getActivityLogsByClassAndId(
        string $className,
        mixed $objectId,
        array $sort = [],
        int $limit = 20,
        int $offset = 0,
    ): array {
        return $this->activityLogStorage->findByClassAndObjectId($className, $objectId, $sort, $limit, $offset);
    }
}
