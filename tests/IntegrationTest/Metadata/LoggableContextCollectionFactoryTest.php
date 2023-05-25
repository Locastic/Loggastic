<?php

namespace Locastic\Loggastic\Tests\IntegrationTest\Metadata;

use Locastic\Loggastic\Metadata\LoggableContext\Factory\LoggableContextCollectionFactoryInterface;
use Locastic\Loggastic\Tests\Fixtures\App\Model\DummyBlogPost;
use Locastic\Loggastic\Tests\Fixtures\App\Model\DummyCategory;
use Locastic\Loggastic\Tests\Fixtures\App\Model\DummyPhoto;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class LoggableContextCollectionFactoryTest extends KernelTestCase
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
                ],
        ], $loggableContextCollection[DummyCategory::class]);

        self::assertEquals([
            'groups' => [
                'dummy_photo_log',
            ],
        ], $loggableContextCollection[DummyPhoto::class]);

        self::assertEquals([
            'groups' => [
                'dummy_blog_post_log',
            ],
        ], $loggableContextCollection[DummyBlogPost::class]);
    }
}
