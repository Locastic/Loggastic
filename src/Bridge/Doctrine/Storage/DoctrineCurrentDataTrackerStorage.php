<?php

namespace Locastic\Loggastic\Bridge\Doctrine\Storage;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Locastic\Loggastic\Model\Input\CurrentDataTrackerInputInterface;
use Locastic\Loggastic\Model\Output\CurrentDataTracker;
use Locastic\Loggastic\Model\Output\CurrentDataTrackerInterface;
use Locastic\Loggastic\Storage\CurrentDataTrackerStorageInterface;

/**
 * Stores current data trackers in a relational database using Doctrine DBAL.
 * All loggable classes share a single table, discriminated by the object_class column.
 * Timestamps are normalized to UTC before writing. Each object keeps a single tracker
 * row: saving an already tracked object updates its row, so message retries and
 * re-running the populate command never hit the unique index.
 */
final class DoctrineCurrentDataTrackerStorage implements CurrentDataTrackerStorageInterface
{
    public const DEFAULT_TABLE = 'loggastic_current_data_tracker';

    public function __construct(
        private readonly Connection $connection,
        private readonly string $table = self::DEFAULT_TABLE,
    ) {
    }

    public function save(CurrentDataTrackerInputInterface $currentDataTracker, string $className): void
    {
        $existingId = $this->connection->createQueryBuilder()
            ->select('id')
            ->from($this->table)
            ->where('object_class = :objectClass')
            ->andWhere('object_id = :objectId')
            ->setParameter('objectClass', $className)
            ->setParameter('objectId', (string) $currentDataTracker->getObjectId())
            ->executeQuery()
            ->fetchOne();

        if (false !== $existingId) {
            $this->update($existingId, $currentDataTracker, $className);

            return;
        }

        $this->connection->insert($this->table, [
            'object_id' => $currentDataTracker->getObjectId(),
            'object_class' => $className,
            'date_time' => \DateTimeImmutable::createFromMutable($currentDataTracker->getDateTime())->setTimezone(new \DateTimeZone('UTC')),
            'data' => $currentDataTracker->getData(),
        ], [
            'date_time' => Types::DATETIME_IMMUTABLE,
        ]);
    }

    public function update(mixed $id, CurrentDataTrackerInputInterface $currentDataTracker, string $className): void
    {
        $this->connection->update($this->table, [
            'date_time' => \DateTimeImmutable::createFromMutable($currentDataTracker->getDateTime())->setTimezone(new \DateTimeZone('UTC')),
            'data' => $currentDataTracker->getData(),
        ], [
            'id' => $id,
            'object_class' => $className,
        ], [
            'date_time' => Types::DATETIME_IMMUTABLE,
        ]);
    }

    /**
     * @param array<int, CurrentDataTrackerInputInterface> $currentDataTrackers
     */
    public function bulkSave(array $currentDataTrackers, string $className): void
    {
        $this->connection->transactional(function () use ($currentDataTrackers, $className): void {
            foreach ($currentDataTrackers as $currentDataTracker) {
                $this->save($currentDataTracker, $className);
            }
        });
    }

    public function findByClassAndObjectId(string $className, mixed $objectId): ?CurrentDataTrackerInterface
    {
        $row = $this->connection->createQueryBuilder()
            ->select('*')
            ->from($this->table)
            ->where('object_class = :objectClass')
            ->andWhere('object_id = :objectId')
            ->setParameter('objectClass', $className)
            ->setParameter('objectId', (string) $objectId)
            ->executeQuery()
            ->fetchAssociative();

        if (false === $row) {
            return null;
        }

        $currentDataTracker = new CurrentDataTracker();
        $currentDataTracker->setId((string) $row['id']);
        $currentDataTracker->setObjectId($row['object_id']);
        $currentDataTracker->setObjectClass($row['object_class']);
        $currentDataTracker->setDateTime(new \DateTime($row['date_time'], new \DateTimeZone('UTC')));
        $currentDataTracker->setData($row['data'] ?? '[]');

        return $currentDataTracker;
    }
}
