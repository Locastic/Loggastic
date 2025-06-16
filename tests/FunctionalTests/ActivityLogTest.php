<?php

namespace Locastic\Loggastic\Tests\FunctionalTests;

use Doctrine\Common\Collections\ArrayCollection;
use Locastic\Loggastic\Bridge\Elasticsearch\Index\ElasticsearchIndexFactoryInterface;
use Locastic\Loggastic\DataProvider\ActivityLogProviderInterface;
use Locastic\Loggastic\Enum\ActivityLogAction;
use Locastic\Loggastic\Logger\ActivityLoggerInterface;
use Locastic\Loggastic\Model\Output\ActivityLogInterface;
use Locastic\Loggastic\Tests\Fixtures\App\Model\DummyBlogPost;
use Locastic\Loggastic\Tests\Fixtures\App\Model\DummyComment;
use Locastic\Loggastic\Tests\Fixtures\App\Model\DummyPhoto;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Uid\Uuid;

class ActivityLogTest extends KernelTestCase
{
    private readonly ActivityLogProviderInterface $activityLogProvider;
    private readonly DummyBlogPost $blogPost;
    private readonly ActivityLoggerInterface $activityLogger;

    public function __construct(?string $name = null)
    {
        parent::__construct($name);

        $container = self::getContainer()->get('test.service_container');

        $this->activityLogProvider = $container->get(ActivityLogProviderInterface::class);
        $this->activityLogger = $container->get(ActivityLoggerInterface::class);

        // prepare elastic index
        $elasticsearchIndexFactory = $container->get(ElasticsearchIndexFactoryInterface::class);
        $elasticsearchIndexFactory->recreateActivityLogIndex(DummyBlogPost::class);
        $elasticsearchIndexFactory->recreateCurrentDataTrackerLogIndex(DummyBlogPost::class);

        $this->blogPost = $this->createDummyBlogPost();
    }

    public function testLogCreate(): void
    {
        $this->activityLogger->logCreatedItem($this->blogPost);

        $activityLogs = $this->activityLogProvider->getActivityLogsByClassAndId(DummyBlogPost::class, 15);

        self::assertCount(1, $activityLogs);

        $createdLog = $activityLogs[0];

        self::assertInstanceOf(ActivityLogInterface::class, $createdLog);
        self::assertEquals(ActivityLogAction::CREATED, $createdLog->getAction());
        self::assertEquals(15, $createdLog->getObjectId());
    }

    public function testEditWithoutChangesShouldNotBeLogged(): void
    {
        $this->activityLogger->logUpdatedItem($this->blogPost);

        $activityLogs = $this->activityLogProvider->getActivityLogsByClassAndId(DummyBlogPost::class, 15);

        self::assertCount(1, $activityLogs);
    }

    public function testEditWithoutChangesShouldBeLoggedWhenRequested(): void
    {
        $this->activityLogger->logUpdatedItem($this->blogPost, 'Custom log name', true);

        $activityLogs = $this->activityLogProvider->getActivityLogsByClassAndId(DummyBlogPost::class, 15);

        self::assertCount(2, $activityLogs);

        $editedLog = $activityLogs[1];

        self::assertInstanceOf(ActivityLogInterface::class, $editedLog);
        self::assertEquals('Custom log name', $editedLog->getAction());
        self::assertEquals(15, $editedLog->getObjectId());

        self::assertNull($editedLog->getDataChanges());
    }

    public function testLogEditRelation(): void
    {
        $this->blogPost->getPhotos()->first()->setPath('https://locastic.com');

        $this->activityLogger->logUpdatedItem($this->blogPost);

        $activityLogs = $this->activityLogProvider->getActivityLogsByClassAndId(DummyBlogPost::class, 15);

        self::assertCount(3, $activityLogs);

        $editedLog = $activityLogs[2];

        self::assertInstanceOf(ActivityLogInterface::class, $editedLog);
        self::assertEquals(ActivityLogAction::EDITED, $editedLog->getAction());
        self::assertEquals(15, $editedLog->getObjectId());

        self::assertEquals([
            'previousValues' => [
                'photos' => [
                    1950 => ['path' => 'path1'],
                ]
            ],
            'currentValues' => [
                'photos' => [
                    1950 => ['path' => 'https://locastic.com'],
                ]
            ],
        ], $editedLog->getDataChanges());
    }

    public function testLogEdit(): void
    {
        $this->blogPost->setTitle('Activity Logs using Elasticsearch');
        $this->blogPost->setTags(['#php', '#locastic', '#elasticSearch']);
        $this->blogPost->setEnabled(true);
        $this->blogPost->getPhotos()->first()->setPath('https://locastic.com/blog');
        $this->blogPost->getComments()->first()->setContent('comment 1 edited');

        $this->activityLogger->logUpdatedItem($this->blogPost);

        $activityLogs = $this->activityLogProvider->getActivityLogsByClassAndId(DummyBlogPost::class, 15);

        self::assertCount(4, $activityLogs);

        $editedLog = $activityLogs[3];

        self::assertInstanceOf(ActivityLogInterface::class, $editedLog);
        self::assertEquals(ActivityLogAction::EDITED, $editedLog->getAction());
        self::assertEquals(15, $editedLog->getObjectId());

        self::assertEquals([
            'previousValues' => [
                'title' => 'Activity logs in Elasticsearch',
                'tags' => [1 => '#elasticsearch'],
                'enabled' => false,
                'photos' => [
                    1950 => ['path' => 'https://locastic.com'],
                ],
                'comments' => [
                    '019777b9-346a-7eb9-b289-1a7327b54dc1' => ['content' => 'comment 1'],
                ],
            ],
            'currentValues' => [
                'title' => 'Activity Logs using Elasticsearch',
                'tags' => [1 => '#locastic', 2 => '#elasticSearch'],
                'enabled' => true,
                'photos' => [
                    1950 => ['path' => 'https://locastic.com/blog'],
                ],
                'comments' => [
                    '019777b9-346a-7eb9-b289-1a7327b54dc1' => ['content' => 'comment 1 edited'],
                ],
            ],
        ], $editedLog->getDataChanges());
    }
    public function testLogProviderByClassLimit(): void
    {
        $activityLogs = $this->activityLogProvider->getActivityLogsByClass(DummyBlogPost::class, [], 1);

        self::assertCount(1, $activityLogs);
    }

    public function testLogProviderByClassAndIdLimit(): void
    {
        $activityLogs = $this->activityLogProvider->getActivityLogsByClassAndId(DummyBlogPost::class, 15, [], 1);

        self::assertCount(1, $activityLogs);
    }

    public function testProviderByClassAndIndexLimit(): void
    {
        $activityLogs = $this->activityLogProvider->getActivityLogsByIndexAndId('dummy_blog_post_activity_log',15, [],1);

        self::assertCount(1, $activityLogs);
    }

    public function testLogProviderByClassLimitAndOffset(): void
    {
        $activityLogs = $this->activityLogProvider->getActivityLogsByClass(DummyBlogPost::class, [], 20, 1);

        $editedLog = reset($activityLogs);

        self::assertCount(3, $activityLogs);
        self::assertInstanceOf(ActivityLogInterface::class, $editedLog);
        self::assertEquals(15, $editedLog->getObjectId());
        self::assertEquals('Custom log name', $editedLog->getAction());
    }

    public function testLogProviderByClassAndIdLimitAndOffset(): void
    {
        $activityLogs = $this->activityLogProvider->getActivityLogsByClassAndId(DummyBlogPost::class, 15, [], 20, 2);

        $editedLog = reset($activityLogs);

        self::assertCount(2, $activityLogs);
        self::assertInstanceOf(ActivityLogInterface::class, $editedLog);
        self::assertEquals(15, $editedLog->getObjectId());
        self::assertEquals(ActivityLogAction::EDITED, $editedLog->getAction());
    }

    public function testProviderByClassAndIndexLimitAndOffset(): void
    {
        $activityLogs = $this->activityLogProvider->getActivityLogsByIndexAndId('dummy_blog_post_activity_log',15, [],20, 3);

        $editedLog = $activityLogs[0];

        self::assertCount(1, $activityLogs);
        self::assertInstanceOf(ActivityLogInterface::class, $editedLog);
        self::assertEquals(15, $editedLog->getObjectId());
        self::assertEquals(ActivityLogAction::EDITED, $editedLog->getAction());
    }

    public function testLogDelete(): void
    {
        $this->activityLogger->logDeletedItem($this->blogPost, $this->blogPost->getId(), DummyBlogPost::class);

        $activityLogs = $this->activityLogProvider->getActivityLogsByClassAndId(DummyBlogPost::class, 15);
        self::assertCount(5, $activityLogs);
        self::assertInstanceOf(ActivityLogInterface::class, $activityLogs[4]);
        self::assertEquals(ActivityLogAction::DELETED, $activityLogs[4]->getAction());
    }

    private function createDummyBlogPost(): DummyBlogPost
    {
        $blogPost = new DummyBlogPost();

        $blogPost->setId(15);
        $blogPost->setEnabled(false);
        $blogPost->setPosition(2);
        $blogPost->setPublishAt(new \DateTime('2022-11-11 15:00:00'));
        $blogPost->setTags(['#php', '#elasticsearch']);
        $blogPost->setTitle('Activity logs in Elasticsearch');

        $photo1 = new DummyPhoto();
        $photo1->setId(1950);
        $photo1->setPath('path1');

        $photo2 = new DummyPhoto();
        $photo2->setId(1911);
        $photo2->setPath('path2');

        $blogPost->setPhotos(new ArrayCollection([$photo1, $photo2]));

        $comment1 = new DummyComment();
        $comment1->setId(Uuid::fromString('019777b9-346a-7eb9-b289-1a7327b54dc1'));
        $comment1->setContent('comment 1');

        $comment2 = new DummyComment();
        $comment2->setId(Uuid::fromString('019777b9-346a-7eb9-b289-1a7327fad6fb'));
        $comment2->setContent('comment 2');

        $blogPost->setComments(new ArrayCollection([$comment1, $comment2]));

        return $blogPost;
    }
}
