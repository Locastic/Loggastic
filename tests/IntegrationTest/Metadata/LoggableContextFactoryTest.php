<?php

namespace Locastic\Loggastic\Tests\IntegrationTest\Metadata;

use Locastic\Loggastic\Metadata\LoggableContext\Factory\LoggableContextFactoryInterface;
use Locastic\Loggastic\Tests\Fixtures\App\Model\DummyCategory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class LoggableContextFactoryTest extends KernelTestCase
{
    public function testCreate(): void
    {
        $loggableContextFactory = self::getContainer()->get(LoggableContextFactoryInterface::class);

        $loggableContext = $loggableContextFactory->create(DummyCategory::class);

        self::assertEquals([
            'groups' => [
                'dummy_category_log',
                'dummy_photo_log',
                ],
        ], $loggableContext);
    }
}
