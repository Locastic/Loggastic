<?php

namespace Locastic\ActivityLog\Tests\IntegrationTest\Metadata;

use Locastic\ActivityLog\Metadata\LoggableContext\Factory\LoggableContextCollectionFactoryInterface;
use Locastic\ActivityLog\Tests\Fixtures\DummyBlogPost;
use Locastic\ActivityLog\Tests\Fixtures\DummyCategory;
use Locastic\ActivityLog\Tests\Fixtures\DummyPhoto;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LoggableContextCollectionFactoryTest extends WebTestCase
{
    public function testCreate()
    {
        $loggableContextFactory = self::getContainer()->get(LoggableContextCollectionFactoryInterface::class);

        $loggableContextCollection = $loggableContextFactory->create();
        $loggableContextCollection = $loggableContextCollection->getIterator();

        self::assertEquals([
            'groups' => [
                'dummy_category_log',
                'dummy_photo_log',
                ]
        ], $loggableContextCollection[DummyCategory::class]);

        self::assertEquals([
            'groups' => [
                'dummy_photo_log',
            ]
        ], $loggableContextCollection[DummyPhoto::class]);

        self::assertEquals([
            'groups' => [
                'dummy_blog_post_log',
            ]
        ], $loggableContextCollection[DummyBlogPost::class]);
    }
}
