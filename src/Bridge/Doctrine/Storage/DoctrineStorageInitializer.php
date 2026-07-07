<?php

namespace Locastic\Loggastic\Bridge\Doctrine\Storage;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Types;
use Locastic\Loggastic\Storage\StorageInitializerInterface;

/**
 * Creates the shared relational tables used by the Doctrine storage. All loggable
 * classes share two tables, so per-class initialization only ensures the tables
 * exist; recreation clears the rows belonging to the class.
 */
final class DoctrineStorageInitializer implements StorageInitializerInterface
{
    public function __construct(
        private readonly Connection $connection,
        private readonly string $activityLogTable = DoctrineActivityLogStorage::DEFAULT_TABLE,
        private readonly string $currentDataTrackerTable = DoctrineCurrentDataTrackerStorage::DEFAULT_TABLE,
    ) {
    }

    public function initializeActivityLogStorage(string $className): bool
    {
        if ($this->tableExists($this->activityLogTable)) {
            return false;
        }

        $table = new Table($this->activityLogTable);
        $table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('object_id', Types::STRING, ['length' => 255]);
        $table->addColumn('object_class', Types::STRING, ['length' => 255]);
        $table->addColumn('action', Types::STRING, ['length' => 255, 'notnull' => false]);
        $table->addColumn('logged_at', Types::DATETIME_IMMUTABLE);
        $table->addColumn('data_changes', Types::JSON, ['notnull' => false]);
        $table->addColumn('request_url', Types::TEXT, ['notnull' => false]);
        $table->addColumn('user_data', Types::JSON, ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['object_class', 'object_id'], 'idx_'.$this->activityLogTable.'_object');
        $table->addIndex(['object_class', 'logged_at'], 'idx_'.$this->activityLogTable.'_logged_at');

        $this->connection->createSchemaManager()->createTable($table);

        return true;
    }

    public function initializeCurrentDataTrackerStorage(string $className): bool
    {
        if ($this->tableExists($this->currentDataTrackerTable)) {
            return false;
        }

        $table = new Table($this->currentDataTrackerTable);
        $table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('object_id', Types::STRING, ['length' => 255]);
        $table->addColumn('object_class', Types::STRING, ['length' => 255]);
        $table->addColumn('date_time', Types::DATETIME_IMMUTABLE);
        $table->addColumn('data', Types::JSON, ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['object_class', 'object_id'], 'uniq_'.$this->currentDataTrackerTable.'_object');

        $this->connection->createSchemaManager()->createTable($table);

        return true;
    }

    public function recreateActivityLogStorage(string $className): void
    {
        $this->initializeActivityLogStorage($className);
        $this->connection->delete($this->activityLogTable, ['object_class' => $className]);
    }

    public function recreateCurrentDataTrackerStorage(string $className): void
    {
        $this->initializeCurrentDataTrackerStorage($className);
        $this->connection->delete($this->currentDataTrackerTable, ['object_class' => $className]);
    }

    private function tableExists(string $table): bool
    {
        return $this->connection->createSchemaManager()->tablesExist([$table]);
    }
}
