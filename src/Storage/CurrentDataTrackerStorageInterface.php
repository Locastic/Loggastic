<?php

namespace Locastic\Loggastic\Storage;

use Locastic\Loggastic\Model\Input\CurrentDataTrackerInputInterface;
use Locastic\Loggastic\Model\Output\CurrentDataTrackerInterface;

/**
 * Persists and retrieves current data trackers, the latest known state of each
 * loggable object used to compute changes when logging updates.
 * Implement this interface to store current data trackers in a custom backend.
 */
interface CurrentDataTrackerStorageInterface
{
    public function save(CurrentDataTrackerInputInterface $currentDataTracker, string $className): void;

    public function update(mixed $id, CurrentDataTrackerInputInterface $currentDataTracker, string $className): void;

    /**
     * @param array<int, CurrentDataTrackerInputInterface> $currentDataTrackers
     */
    public function bulkSave(array $currentDataTrackers, string $className): void;

    public function findByClassAndObjectId(string $className, mixed $objectId): ?CurrentDataTrackerInterface;
}
