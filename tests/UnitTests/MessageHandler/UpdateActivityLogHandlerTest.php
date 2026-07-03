<?php

namespace Locastic\Loggastic\Tests\UnitTests\MessageHandler;

use Locastic\Loggastic\DataProcessor\ActivityLogProcessorInterface;
use Locastic\Loggastic\DataProvider\CurrentDataTrackerProviderInterface;
use Locastic\Loggastic\Message\UpdateActivityLogMessage;
use Locastic\Loggastic\MessageHandler\UpdateActivityLogHandler;
use Locastic\Loggastic\Metadata\LoggableContext\Factory\LoggableContextCollectionFactoryInterface;
use Locastic\Loggastic\Metadata\LoggableContext\Factory\LoggableContextFactory;
use Locastic\Loggastic\Metadata\LoggableContext\LoggableContextCollection;
use PHPUnit\Framework\TestCase;

class UpdateActivityLogHandlerTest extends TestCase
{
    public function testItSkipsItemsWithoutAnIdentifier(): void
    {
        $item = new class {
            public function getId(): ?int
            {
                return null;
            }
        };

        $collectionFactory = $this->createMock(LoggableContextCollectionFactoryInterface::class);
        $collectionFactory->method('create')->willReturn(
            new LoggableContextCollection([$item::class => ['groups' => ['test_log']]])
        );

        $currentDataTrackerProvider = $this->createMock(CurrentDataTrackerProviderInterface::class);
        $currentDataTrackerProvider->expects(self::never())->method('getCurrentDataTrackerByClassAndId');

        $activityLogProcessor = $this->createMock(ActivityLogProcessorInterface::class);
        $activityLogProcessor->expects(self::never())->method('processUpdatedItem');

        $handler = new UpdateActivityLogHandler(
            $activityLogProcessor,
            $currentDataTrackerProvider,
            new LoggableContextFactory($collectionFactory)
        );

        $handler(new UpdateActivityLogMessage($item));
    }
}
