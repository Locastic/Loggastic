<?php

namespace Locastic\Loggastic\Storage;

/**
 * Creates the storage a loggable class writes to (an index, a table, ...)
 * before any logs are written. Implementations must be safe to run repeatedly.
 */
interface StorageInitializerInterface
{
    /**
     * @return bool true if the storage was created, false if it already existed
     */
    public function initializeActivityLogStorage(string $className): bool;

    /**
     * @return bool true if the storage was created, false if it already existed
     */
    public function initializeCurrentDataTrackerStorage(string $className): bool;

    /**
     * Drops existing activity log storage and creates it from scratch.
     */
    public function recreateActivityLogStorage(string $className): void;

    /**
     * Drops existing current data tracker storage and creates it from scratch.
     */
    public function recreateCurrentDataTrackerStorage(string $className): void;
}
