<?php

namespace Locastic\ActivityLog\Tests\IntegrationTest\Metadata;

use Locastic\ActivityLog\Metadata\LoggableContext\Factory\LoggableContextFactoryInterface;
use Locastic\ActivityLog\Tests\Fixtures\DummyCategory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LoggableContextFactoryTest extends WebTestCase
{
    public function testCreate(): void
    {
        $loggableContextFactory = self::getContainer()->get(LoggableContextFactoryInterface::class);

        $loggableContext = $loggableContextFactory->create(DummyCategory::class);

        self::assertEquals([
            'groups' => [
                'dummy_category_log',
                'dummy_photo_log',
                ]
        ], $loggableContext);
    }
}
