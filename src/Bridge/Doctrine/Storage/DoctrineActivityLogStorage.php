<?php

namespace Locastic\Loggastic\Bridge\Doctrine\Storage;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Locastic\Loggastic\Model\Input\ActivityLogInputInterface;
use Locastic\Loggastic\Model\Output\ActivityLog;
use Locastic\Loggastic\Model\Output\ActivityLogInterface;
use Locastic\Loggastic\Storage\ActivityLogStorageInterface;

/**
 * Stores activity logs in a relational database using Doctrine DBAL.
 * All loggable classes share a single table, discriminated by the object_class column.
 * Timestamps are normalized to UTC before writing.
 */
final class DoctrineActivityLogStorage implements ActivityLogStorageInterface
{
    public const DEFAULT_TABLE = 'loggastic_activity_log';

    private const SORTABLE_FIELDS = ['loggedAt' => 'logged_at', 'action' => 'action', 'objectId' => 'object_id'];

    public function __construct(
        private readonly Connection $connection,
        private readonly string $table = self::DEFAULT_TABLE,
    ) {
    }

    public function save(ActivityLogInputInterface $activityLog, string $className): void
    {
        $this->connection->insert($this->table, [
            'object_id' => $activityLog->getObjectId(),
            'object_class' => $className,
            'action' => $activityLog->getAction(),
            'logged_at' => \DateTimeImmutable::createFromMutable($activityLog->getLoggedAt())->setTimezone(new \DateTimeZone('UTC')),
            'data_changes' => $activityLog->getDataChanges(),
            'request_url' => $activityLog->getRequestUrl(),
            'user_data' => null !== $activityLog->getUser() ? json_encode($activityLog->getUser(), JSON_THROW_ON_ERROR) : null,
        ], [
            'logged_at' => Types::DATETIME_IMMUTABLE,
        ]);
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

    /**
     * @param array<string, string> $sort
     *
     * @return array<int, ActivityLogInterface>
     */
    private function query(string $className, mixed $objectId, array $sort, int $limit, int $offset): array
    {
        $queryBuilder = $this->connection->createQueryBuilder()
            ->select('*')
            ->from($this->table)
            ->where('object_class = :objectClass')
            ->setParameter('objectClass', $className)
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        if (null !== $objectId) {
            $queryBuilder->andWhere('object_id = :objectId')->setParameter('objectId', (string) $objectId);
        }

        foreach ($sort as $field => $direction) {
            if (isset(self::SORTABLE_FIELDS[$field])) {
                $queryBuilder->addOrderBy(self::SORTABLE_FIELDS[$field], 'desc' === strtolower((string) $direction) ? 'DESC' : 'ASC');
            }
        }

        // deterministic order and tiebreak, insertion order matches ES behavior
        $queryBuilder->addOrderBy('id', 'ASC');

        return array_map($this->hydrate(...), $queryBuilder->executeQuery()->fetchAllAssociative());
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrate(array $row): ActivityLog
    {
        $activityLog = new ActivityLog();
        $activityLog->setId((string) $row['id']);
        $activityLog->setAction($row['action']);
        $activityLog->setLoggedAt(new \DateTime($row['logged_at'], new \DateTimeZone('UTC')));
        $activityLog->setObjectId($row['object_id']);
        $activityLog->setObjectClass($row['object_class']);
        $activityLog->setDataChanges($row['data_changes']);
        $activityLog->setUser(null !== $row['user_data'] ? json_decode($row['user_data'], true, 512, JSON_THROW_ON_ERROR) : null);
        $activityLog->setRequestUrl($row['request_url']);

        return $activityLog;
    }
}
