<?php

namespace Locastic\Loggastic\Tests\FunctionalTests;

use Doctrine\Common\Collections\ArrayCollection;
use Locastic\Loggastic\DataProvider\ActivityLogProviderInterface;
use Locastic\Loggastic\Enum\ActivityLogAction;
use Locastic\Loggastic\Logger\ActivityLoggerInterface;
use Locastic\Loggastic\Storage\ActivityLogStorageInterface;
use Locastic\Loggastic\Storage\InMemory\InMemoryActivityLogStorage;
use Locastic\Loggastic\Tests\Fixtures\App\Model\DummyBlogPost;
use Locastic\Loggastic\Tests\Fixtures\App\Model\DummyPhoto;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Runs the full logging flow with the bundle configured for in-memory storage,
 * proving the storage config option wires a non-Elasticsearch backend end to end.
 */
class InMemoryStorageActivityLogTest extends KernelTestCase
{
    private ActivityLoggerInterface $activityLogger;
    private ActivityLogProviderInterface $activityLogProvider;

    protected function setUp(): void
    {
        $_SERVER['APP_ENV'] = 'in_memory_storage';

        self::bootKernel(['environment' => 'in_memory_storage']);

        $container = self::getContainer();

        $this->activityLogger = $container->get(ActivityLoggerInterface::class);
        $this->activityLogProvider = $container->get(ActivityLogProviderInterface::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $_SERVER['APP_ENV'] = 'test';
    }

    public function testStorageInterfacesAreAliasedToInMemoryImplementations(): void
    {
        self::assertInstanceOf(
            InMemoryActivityLogStorage::class,
            self::getContainer()->get(ActivityLogStorageInterface::class)
        );
    }

    public function testFullActivityLogFlow(): void
    {
        $blogPost = $this->createDummyBlogPost();

        $this->activityLogger->logCreatedItem($blogPost);

        $activityLogs = $this->activityLogProvider->getActivityLogsByClassAndId(DummyBlogPost::class, 15);

        self::assertCount(1, $activityLogs);
        self::assertEquals(ActivityLogAction::CREATED, $activityLogs[0]->getAction());
        self::assertEquals(15, $activityLogs[0]->getObjectId());

        // no-op update must not be logged
        $this->activityLogger->logUpdatedItem($blogPost);

        self::assertCount(1, $this->activityLogProvider->getActivityLogsByClassAndId(DummyBlogPost::class, 15));

        $blogPost->setTitle('Activity logs without Elasticsearch');
        $this->activityLogger->logUpdatedItem($blogPost);

        $activityLogs = $this->activityLogProvider->getActivityLogsByClassAndId(DummyBlogPost::class, 15);

        self::assertCount(2, $activityLogs);
        self::assertEquals(ActivityLogAction::EDITED, $activityLogs[1]->getAction());
        self::assertEquals([
            'previousValues' => ['title' => 'Activity logs in memory'],
            'currentValues' => ['title' => 'Activity logs without Elasticsearch'],
        ], $activityLogs[1]->getDataChanges());

        $this->activityLogger->logDeletedItem($blogPost, $blogPost->getId(), DummyBlogPost::class);

        $activityLogs = $this->activityLogProvider->getActivityLogsByClassAndId(DummyBlogPost::class, 15);

        self::assertCount(3, $activityLogs);
        self::assertEquals(ActivityLogAction::DELETED, $activityLogs[2]->getAction());
    }

    private function createDummyBlogPost(): DummyBlogPost
    {
        $blogPost = new DummyBlogPost();

        $blogPost->setId(15);
        $blogPost->setEnabled(false);
        $blogPost->setPosition(2);
        $blogPost->setPublishAt(new \DateTime('2022-11-11 15:00:00'));
        $blogPost->setTags(['#php', '#loggastic']);
        $blogPost->setTitle('Activity logs in memory');

        $photo = new DummyPhoto();
        $photo->setId(1950);
        $photo->setPath('path1');

        $blogPost->setPhotos(new ArrayCollection([$photo]));

        return $blogPost;
    }
}
