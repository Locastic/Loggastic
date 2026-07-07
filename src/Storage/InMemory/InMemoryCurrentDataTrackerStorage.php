<?php

namespace Locastic\Loggastic\Storage\InMemory;

use Locastic\Loggastic\Model\Input\CurrentDataTrackerInputInterface;
use Locastic\Loggastic\Model\Output\CurrentDataTracker;
use Locastic\Loggastic\Model\Output\CurrentDataTrackerInterface;
use Locastic\Loggastic\Storage\CurrentDataTrackerStorageInterface;

/**
 * Keeps current data trackers in memory. Intended for test suites that need the
 * full logging flow without a real storage backend; trackers do not survive the process.
 * Each object keeps a single tracker: saving an already tracked object updates it.
 */
final class InMemoryCurrentDataTrackerStorage implements CurrentDataTrackerStorageInterface
{
    /**
     * @var array<string, array<int, CurrentDataTracker>>
     */
    private array $trackers = [];

    private int $nextId = 1;

    public function save(CurrentDataTrackerInputInterface $currentDataTracker, string $className): void
    {
        foreach ($this->trackers[$className] ?? [] as $existingTracker) {
            if ($existingTracker->getObjectId() === (string) $currentDataTracker->getObjectId()) {
                $this->apply($existingTracker, $currentDataTracker);

                return;
            }
        }

        $tracker = new CurrentDataTracker();
        $tracker->setId((string) $this->nextId++);
        $tracker->setObjectId($currentDataTracker->getObjectId());
        $tracker->setObjectClass($className);
        $this->apply($tracker, $currentDataTracker);

        $this->trackers[$className][] = $tracker;
    }

    public function update(mixed $id, CurrentDataTrackerInputInterface $currentDataTracker, string $className): void
    {
        foreach ($this->trackers[$className] ?? [] as $tracker) {
            if ((string) $tracker->getId() === (string) $id) {
                $this->apply($tracker, $currentDataTracker);

                return;
            }
        }
    }

    /**
     * @param array<int, CurrentDataTrackerInputInterface> $currentDataTrackers
     */
    public function bulkSave(array $currentDataTrackers, string $className): void
    {
        foreach ($currentDataTrackers as $currentDataTracker) {
            $this->save($currentDataTracker, $className);
        }
    }

    public function findByClassAndObjectId(string $className, mixed $objectId): ?CurrentDataTrackerInterface
    {
        foreach ($this->trackers[$className] ?? [] as $tracker) {
            if ($tracker->getObjectId() === (string) $objectId) {
                return $tracker;
            }
        }

        return null;
    }

    public function clear(string $className): void
    {
        unset($this->trackers[$className]);
    }

    private function apply(CurrentDataTracker $tracker, CurrentDataTrackerInputInterface $input): void
    {
        $tracker->setDateTime(clone $input->getDateTime());
        $tracker->setData($input->getData() ?? '[]');
    }
}
