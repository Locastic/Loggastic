<?php

namespace Locastic\Loggastic\Tests\FunctionalTests;

use Locastic\Loggastic\Bridge\Elasticsearch\Index\ElasticsearchIndexFactoryInterface;
use Locastic\Loggastic\DataProvider\ActivityLogProviderInterface;
use Locastic\Loggastic\Enum\ActivityLogAction;
use Locastic\Loggastic\Logger\ActivityLoggerInterface;
use Locastic\Loggastic\Model\ActivityLogInterface;
use Locastic\Loggastic\Tests\Fixtures\App\Model\DummyBlogPost;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ActivityLogTest extends KernelTestCase
{
    private ActivityLogProviderInterface $activityLogProvider;
    private DummyBlogPost $blogPost;
    private ActivityLoggerInterface $activityLogger;

    public function __construct(string $name)
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

        $editedLog = $activityLogs[0];
        self::assertCount(1, $activityLogs);
        self::assertInstanceOf(ActivityLogInterface::class, $editedLog);
        self::assertEquals(ActivityLogAction::CREATED, $editedLog->getAction());
        self::assertEquals(15, $editedLog->getObjectId());
    }

    public function testLogEdit(): void
    {
        $this->blogPost->setTitle('Activity Logs using Elasticsearch');
        $this->blogPost->setTags(['#php', '#elasticSearch']);
        $this->blogPost->setEnabled(true);

        $this->activityLogger->logUpdatedItem($this->blogPost);

        $activityLogs = $this->activityLogProvider->getActivityLogsByClassAndId(DummyBlogPost::class, 15);

        $editedLog = $activityLogs[1];
        self::assertCount(2, $activityLogs);
        self::assertInstanceOf(ActivityLogInterface::class, $editedLog);
        self::assertEquals(ActivityLogAction::EDITED, $editedLog->getAction());
        self::assertEquals(15, $editedLog->getObjectId());

        self::assertEquals([
            'previousValues' => [
                'title' => 'Activity logs in Elasticsearch',
                'tags' => [1 => '#elasticsearch'],
                'enabled' => false,
            ],
            'currentValues' => [
                'title' => 'Activity Logs using Elasticsearch',
                'tags' => [1 => '#elasticSearch'],
                'enabled' => true,
            ],
        ], $editedLog->getDataChangesArray());
    }

    public function testLogDelete(): void
    {
        $this->activityLogger->logDeletedItem($this->blogPost->getId(), DummyBlogPost::class);

        $activityLogs = $this->activityLogProvider->getActivityLogsByClassAndId(DummyBlogPost::class, 15);
        self::assertCount(3, $activityLogs);
        self::assertInstanceOf(ActivityLogInterface::class, $activityLogs[2]);
        self::assertEquals(ActivityLogAction::DELETED, $activityLogs[2]->getAction());
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

        return $blogPost;
    }
}
