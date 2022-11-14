<?php

namespace Locastic\ActivityLog\Tests\FunctionalTests;

use Locastic\ActivityLog\Bridge\Elasticsearch\Index\ElasticsearchIndexFactoryInterface;
use Locastic\ActivityLog\DataProvider\ActivityLogProviderInterface;
use Locastic\ActivityLog\Enum\ActivityLogAction;
use Locastic\ActivityLog\Message\CreateActivityLogMessage;
use Locastic\ActivityLog\Message\DeleteActivityLogMessage;
use Locastic\ActivityLog\Message\UpdateActivityLogMessage;
use Locastic\ActivityLog\Model\ActivityLogInterface;
use Locastic\ActivityLog\Tests\Fixtures\DummyBlogPost;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Messenger\MessageBusInterface;

class ActivityLogTest extends WebTestCase
{
    private MessageBusInterface $bus;
    private ActivityLogProviderInterface $activityLogProvider;
    private DummyBlogPost $blogPost;

    public function __construct()
    {
        parent::__construct();

        $container = self::getContainer()->get('test.service_container');

        $this->bus = $container->get(MessageBusInterface::class);
        $this->activityLogProvider = $container->get(ActivityLogProviderInterface::class);

        // prepare elastic index
        $elasticsearchIndexFactory = $container->get(ElasticsearchIndexFactoryInterface::class);
        $elasticsearchIndexFactory->recreateActivityLogIndex(DummyBlogPost::class);
        $elasticsearchIndexFactory->recreateCurrentDataTrackerLogIndex(DummyBlogPost::class);

        $this->blogPost = $this->createDummyBlogPost();
    }

    public function testLogCreate(): void
    {
        $this->bus->dispatch(new CreateActivityLogMessage($this->blogPost));

        $activityLogs = $this->activityLogProvider->getActivityLogsByClassAndId(DummyBlogPost::class, 15);

        $editedLog = $activityLogs[0];
        self::assertCount(1, $activityLogs);
        self::assertInstanceOf(ActivityLogInterface::class, $editedLog);
        self::assertEquals(ActivityLogAction::$CREATED, $editedLog->getAction());
        self::assertEquals(15, $editedLog->getObjectId());
    }

    public function testLogEdit(): void
    {
        $this->blogPost->setTitle('Activity Logs using Elasticsearch');
        $this->blogPost->setTags(['#php', '#elasticSearch']);
        $this->blogPost->setEnabled(true);

        $this->bus->dispatch(new UpdateActivityLogMessage($this->blogPost));

        $activityLogs = $this->activityLogProvider->getActivityLogsByClassAndId(DummyBlogPost::class, 15);

        $editedLog = $activityLogs[1];
        self::assertCount(2, $activityLogs);
        self::assertInstanceOf(ActivityLogInterface::class, $editedLog);
        self::assertEquals(ActivityLogAction::$EDITED, $editedLog->getAction());
        self::assertEquals(15, $editedLog->getObjectId());

        self::assertEquals([
            'previousValues' => [
                'title' => 'Activity logs in Elasticsearch',
                'tags' => [1 => '#elasticsearch'],
                'enabled' => false
            ],
            'currentValues' => [
                'title' => 'Activity Logs using Elasticsearch',
                'tags' => [1 => '#elasticSearch'],
                'enabled' => true
            ],
        ], $editedLog->getData());
    }

    public function testLogDelete(): void
    {
        $this->bus->dispatch(new DeleteActivityLogMessage(DummyBlogPost::class, 15));

        $activityLogs = $this->activityLogProvider->getActivityLogsByClassAndId(DummyBlogPost::class, 15);
        self::assertCount(3, $activityLogs);
        self::assertInstanceOf(ActivityLogInterface::class, $activityLogs[2]);
        self::assertEquals(ActivityLogAction::$DELETED, $activityLogs[2]->getAction());
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
