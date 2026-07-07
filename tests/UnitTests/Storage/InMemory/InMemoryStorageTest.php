<?php

namespace Locastic\Loggastic\Tests\UnitTests\Storage\InMemory;

use Locastic\Loggastic\Model\Input\ActivityLogInput;
use Locastic\Loggastic\Model\Input\CurrentDataTrackerInput;
use Locastic\Loggastic\Storage\InMemory\InMemoryActivityLogStorage;
use Locastic\Loggastic\Storage\InMemory\InMemoryCurrentDataTrackerStorage;
use Locastic\Loggastic\Storage\InMemory\InMemoryStorageInitializer;
use PHPUnit\Framework\TestCase;

class InMemoryStorageTest extends TestCase
{
    private InMemoryActivityLogStorage $activityLogStorage;
    private InMemoryCurrentDataTrackerStorage $currentDataTrackerStorage;
    private InMemoryStorageInitializer $initializer;

    protected function setUp(): void
    {
        $this->activityLogStorage = new InMemoryActivityLogStorage();
        $this->currentDataTrackerStorage = new InMemoryCurrentDataTrackerStorage();
        $this->initializer = new InMemoryStorageInitializer($this->activityLogStorage, $this->currentDataTrackerStorage);
    }

    public function testInitializeReportsWhetherStorageWasCreated(): void
    {
        self::assertTrue($this->initializer->initializeActivityLogStorage('Some\Class'));
        self::assertFalse($this->initializer->initializeActivityLogStorage('Some\Class'));

        self::assertTrue($this->initializer->initializeCurrentDataTrackerStorage('Some\Class'));
        self::assertFalse($this->initializer->initializeCurrentDataTrackerStorage('Some\Class'));
    }

    public function testActivityLogRoundTrip(): void
    {
        $this->activityLogStorage->save($this->createActivityLogInput(
            '15',
            'created',
            new \DateTime('2026-07-03 10:00:00'),
            '{"currentValues":{"title":"foo"}}',
            ['username' => 'paula'],
            'https://example.com/posts/15'
        ), 'Some\Class');

        $logs = $this->activityLogStorage->findByClassAndObjectId('Some\Class', 15);

        self::assertCount(1, $logs);

        $log = $logs[0];

        self::assertNotEmpty($log->getId());
        self::assertSame('created', $log->getAction());
        self::assertSame('15', $log->getObjectId());
        self::assertSame('Some\Class', $log->getObjectClass());
        self::assertEquals(['currentValues' => ['title' => 'foo']], $log->getDataChanges());
        self::assertSame(['username' => 'paula'], $log->getUser());
        self::assertSame('https://example.com/posts/15', $log->getRequestUrl());
    }

    public function testFindByClassFiltersSortsAndPaginates(): void
    {
        foreach ([['15', 'created', '10:00'], ['15', 'edited', '11:00'], ['16', 'created', '12:00'], ['15', 'deleted', '13:00']] as [$objectId, $action, $time]) {
            $this->activityLogStorage->save($this->createActivityLogInput($objectId, $action, new \DateTime('2026-07-03 '.$time)), 'Some\Class');
        }
        $this->activityLogStorage->save($this->createActivityLogInput('15', 'created', new \DateTime()), 'Another\Class');

        self::assertCount(4, $this->activityLogStorage->findByClass('Some\Class'));
        self::assertCount(1, $this->activityLogStorage->findByClass('Another\Class'));
        self::assertCount(3, $this->activityLogStorage->findByClassAndObjectId('Some\Class', 15));

        $sorted = $this->activityLogStorage->findByClass('Some\Class', ['loggedAt' => 'desc']);
        self::assertSame(['deleted', 'created', 'edited', 'created'], array_map(static fn ($log) => $log->getAction(), $sorted));

        $page = $this->activityLogStorage->findByClass('Some\Class', ['loggedAt' => 'desc'], 2, 1);
        self::assertSame(['created', 'edited'], array_map(static fn ($log) => $log->getAction(), $page));

        $unknownSortField = $this->activityLogStorage->findByClass('Some\Class', ['nonexistent' => 'desc']);
        self::assertCount(4, $unknownSortField);
    }

    public function testDefaultOrderIsInsertionOrder(): void
    {
        $sameInstant = new \DateTime('2026-07-03 10:00:00');

        foreach (['created', 'edited', 'deleted'] as $action) {
            $this->activityLogStorage->save($this->createActivityLogInput('15', $action, $sameInstant), 'Some\Class');
        }

        $logs = $this->activityLogStorage->findByClass('Some\Class', ['loggedAt' => 'asc']);

        self::assertSame(['created', 'edited', 'deleted'], array_map(static fn ($log) => $log->getAction(), $logs));
    }

    public function testRecreateActivityLogStorageOnlyClearsGivenClass(): void
    {
        $this->activityLogStorage->save($this->createActivityLogInput('15', 'created', new \DateTime()), 'Some\Class');
        $this->activityLogStorage->save($this->createActivityLogInput('15', 'created', new \DateTime()), 'Another\Class');

        $this->initializer->recreateActivityLogStorage('Some\Class');

        self::assertCount(0, $this->activityLogStorage->findByClass('Some\Class'));
        self::assertCount(1, $this->activityLogStorage->findByClass('Another\Class'));
    }

    public function testCurrentDataTrackerRoundTripAndUpdate(): void
    {
        $this->currentDataTrackerStorage->save($this->createTrackerInput('15', '{"title":"foo"}'), 'Some\Class');

        self::assertNull($this->currentDataTrackerStorage->findByClassAndObjectId('Some\Class', 16));
        self::assertNull($this->currentDataTrackerStorage->findByClassAndObjectId('Another\Class', 15));

        $tracker = $this->currentDataTrackerStorage->findByClassAndObjectId('Some\Class', 15);

        self::assertNotNull($tracker);
        self::assertSame('15', $tracker->getObjectId());
        self::assertSame('Some\Class', $tracker->getObjectClass());
        self::assertEquals(['title' => 'foo'], $tracker->getData());

        $this->currentDataTrackerStorage->update($tracker->getId(), $this->createTrackerInput('15', '{"title":"bar"}'), 'Some\Class');

        $updated = $this->currentDataTrackerStorage->findByClassAndObjectId('Some\Class', 15);
        self::assertNotNull($updated);
        self::assertEquals(['title' => 'bar'], $updated->getData());
        self::assertSame($tracker->getId(), $updated->getId());
    }

    public function testCurrentDataTrackerBulkSave(): void
    {
        $this->currentDataTrackerStorage->bulkSave([
            $this->createTrackerInput('15', '{"title":"foo"}'),
            $this->createTrackerInput('16', '{"title":"bar"}'),
        ], 'Some\Class');

        self::assertNotNull($this->currentDataTrackerStorage->findByClassAndObjectId('Some\Class', 15));
        self::assertNotNull($this->currentDataTrackerStorage->findByClassAndObjectId('Some\Class', 16));
    }

    public function testSavingAnAlreadyTrackedObjectUpdatesTheExistingTracker(): void
    {
        $this->currentDataTrackerStorage->save($this->createTrackerInput('15', '{"title":"foo"}'), 'Some\Class');

        $original = $this->currentDataTrackerStorage->findByClassAndObjectId('Some\Class', 15);
        self::assertNotNull($original);

        $this->currentDataTrackerStorage->save($this->createTrackerInput('15', '{"title":"bar"}'), 'Some\Class');

        $tracker = $this->currentDataTrackerStorage->findByClassAndObjectId('Some\Class', 15);

        self::assertNotNull($tracker);
        self::assertEquals(['title' => 'bar'], $tracker->getData());
        self::assertSame($original->getId(), $tracker->getId());
    }

    public function testRecreateCurrentDataTrackerStorageOnlyClearsGivenClass(): void
    {
        $this->currentDataTrackerStorage->save($this->createTrackerInput('15', '{}'), 'Some\Class');
        $this->currentDataTrackerStorage->save($this->createTrackerInput('15', '{}'), 'Another\Class');

        $this->initializer->recreateCurrentDataTrackerStorage('Some\Class');

        self::assertNull($this->currentDataTrackerStorage->findByClassAndObjectId('Some\Class', 15));
        self::assertNotNull($this->currentDataTrackerStorage->findByClassAndObjectId('Another\Class', 15));
    }

    private function createActivityLogInput(string $objectId, string $action, \DateTime $loggedAt, ?string $dataChanges = null, ?array $user = null, ?string $requestUrl = null): ActivityLogInput
    {
        $input = new ActivityLogInput();
        $input->setObjectId($objectId);
        $input->setAction($action);
        $input->setLoggedAt($loggedAt);
        $input->setDataChanges($dataChanges);
        $input->setUser($user);
        $input->setRequestUrl($requestUrl);

        return $input;
    }

    private function createTrackerInput(string $objectId, string $data): CurrentDataTrackerInput
    {
        $input = new CurrentDataTrackerInput();
        $input->setObjectId($objectId);
        $input->setData($data);
        $input->setDateTime(new \DateTime('2026-07-03 10:00:00'));

        return $input;
    }
}
