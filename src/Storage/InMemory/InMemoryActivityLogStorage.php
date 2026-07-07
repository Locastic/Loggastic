<?php

namespace Locastic\Loggastic\Storage\InMemory;

use Locastic\Loggastic\Model\Input\ActivityLogInputInterface;
use Locastic\Loggastic\Model\Output\ActivityLog;
use Locastic\Loggastic\Model\Output\ActivityLogInterface;
use Locastic\Loggastic\Storage\ActivityLogStorageInterface;

/**
 * Keeps activity logs in memory. Intended for test suites that need the full
 * logging flow without a real storage backend; logs do not survive the process.
 */
final class InMemoryActivityLogStorage implements ActivityLogStorageInterface
{
    /**
     * @var array<string, array<int, ActivityLogInterface>>
     */
    private array $logs = [];

    private int $nextId = 1;

    public function save(ActivityLogInputInterface $activityLog, string $className): void
    {
        $log = new ActivityLog();
        $log->setId((string) $this->nextId++);
        $log->setAction($activityLog->getAction());
        $log->setLoggedAt(clone $activityLog->getLoggedAt());
        $log->setObjectId($activityLog->getObjectId());
        $log->setObjectClass($className);
        $log->setDataChanges($activityLog->getDataChanges());
        $log->setUser($activityLog->getUser());
        $log->setRequestUrl($activityLog->getRequestUrl());

        $this->logs[$className][] = $log;
    }

    /**
     * @param array<string, string> $sort
     *
     * @return array<int, ActivityLogInterface>
     */
    public function findByClass(string $className, array $sort = [], int $limit = 20, int $offset = 0): array
    {
        return $this->query($className, null, $sort, $limit, $offset);
    }

    /**
     * @param array<string, string> $sort
     *
     * @return array<int, ActivityLogInterface>
     */
    public function findByClassAndObjectId(string $className, mixed $objectId, array $sort = [], int $limit = 20, int $offset = 0): array
    {
        return $this->query($className, $objectId, $sort, $limit, $offset);
    }

    public function clear(string $className): void
    {
        unset($this->logs[$className]);
    }

    /**
     * @param array<string, string> $sort
     *
     * @return array<int, ActivityLogInterface>
     */
    private function query(string $className, mixed $objectId, array $sort, int $limit, int $offset): array
    {
        $logs = $this->logs[$className] ?? [];

        if (null !== $objectId) {
            $logs = array_filter($logs, static fn (ActivityLogInterface $log) => $log->getObjectId() === (string) $objectId);
        }

        $logs = array_values($logs);
        usort($logs, self::comparator($sort));

        return array_slice($logs, $offset, $limit);
    }

    /**
     * @param array<string, string> $sort
     */
    private static function comparator(array $sort): callable
    {
        $extractors = [
            'loggedAt' => static fn (ActivityLogInterface $log) => $log->getLoggedAt()->getTimestamp(),
            'action' => static fn (ActivityLogInterface $log) => $log->getAction(),
            'objectId' => static fn (ActivityLogInterface $log) => $log->getObjectId(),
        ];

        return static function (ActivityLogInterface $a, ActivityLogInterface $b) use ($sort, $extractors): int {
            foreach ($sort as $field => $direction) {
                if (!isset($extractors[$field])) {
                    continue;
                }

                $comparison = $extractors[$field]($a) <=> $extractors[$field]($b);

                if (0 !== $comparison) {
                    return 'desc' === strtolower((string) $direction) ? -$comparison : $comparison;
                }
            }

            // deterministic order and tiebreak, insertion order matches ES behavior
            return (int) $a->getId() <=> (int) $b->getId();
        };
    }
}
