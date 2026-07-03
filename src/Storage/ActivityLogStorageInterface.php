<?php

namespace Locastic\Loggastic\Storage;

use Locastic\Loggastic\Model\Input\ActivityLogInputInterface;
use Locastic\Loggastic\Model\Output\ActivityLogInterface;

/**
 * Persists and retrieves activity logs for loggable classes.
 * Implement this interface to store activity logs in a custom backend.
 */
interface ActivityLogStorageInterface
{
    public function save(ActivityLogInputInterface $activityLog, string $className): void;

    /**
     * @param array<string, string> $sort map of field name to direction ('asc' or 'desc')
     *
     * @return array<int, ActivityLogInterface>
     */
    public function findByClass(string $className, array $sort = [], int $limit = 20, int $offset = 0): array;

    /**
     * @param array<string, string> $sort map of field name to direction ('asc' or 'desc')
     *
     * @return array<int, ActivityLogInterface>
     */
    public function findByClassAndObjectId(string $className, mixed $objectId, array $sort = [], int $limit = 20, int $offset = 0): array;
}
