<?php

namespace Locastic\Loggastic\Storage\InMemory;

use Locastic\Loggastic\Storage\StorageInitializerInterface;

/**
 * In-memory storage needs no real initialization; this tracks which classes were
 * initialized so the contract is honored, and recreation clears the stored entries.
 */
final class InMemoryStorageInitializer implements StorageInitializerInterface
{
    /**
     * @var array<string, bool>
     */
    private array $initializedActivityLogs = [];

    /**
     * @var array<string, bool>
     */
    private array $initializedCurrentDataTrackers = [];

    public function __construct(
        private readonly InMemoryActivityLogStorage $activityLogStorage,
        private readonly InMemoryCurrentDataTrackerStorage $currentDataTrackerStorage,
    ) {
    }

    public function initializeActivityLogStorage(string $className): bool
    {
        if (isset($this->initializedActivityLogs[$className])) {
            return false;
        }

        $this->initializedActivityLogs[$className] = true;

        return true;
    }

    public function initializeCurrentDataTrackerStorage(string $className): bool
    {
        if (isset($this->initializedCurrentDataTrackers[$className])) {
            return false;
        }

        $this->initializedCurrentDataTrackers[$className] = true;

        return true;
    }

    public function recreateActivityLogStorage(string $className): void
    {
        $this->initializedActivityLogs[$className] = true;
        $this->activityLogStorage->clear($className);
    }

    public function recreateCurrentDataTrackerStorage(string $className): void
    {
        $this->initializedCurrentDataTrackers[$className] = true;
        $this->currentDataTrackerStorage->clear($className);
    }
}
