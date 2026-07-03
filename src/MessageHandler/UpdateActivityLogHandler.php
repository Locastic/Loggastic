<?php

namespace Locastic\Loggastic\MessageHandler;

use Locastic\Loggastic\DataProcessor\ActivityLogProcessorInterface;
use Locastic\Loggastic\DataProvider\CurrentDataTrackerProviderInterface;
use Locastic\Loggastic\Message\UpdateActivityLogMessageInterface;
use Locastic\Loggastic\Metadata\LoggableContext\Factory\LoggableContextFactory;
use Locastic\Loggastic\Model\Output\CurrentDataTrackerInterface;
use Locastic\Loggastic\Serializer\Traits\NormalizationContextTrait;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class UpdateActivityLogHandler
{
    use NormalizationContextTrait;

    public function __construct(
        private readonly ActivityLogProcessorInterface $activityLogProcessor,
        private readonly CurrentDataTrackerProviderInterface $currentDataTrackerProvider,
        private readonly LoggableContextFactory $loggableContextFactory,
    ) {
    }

    public function __invoke(UpdateActivityLogMessageInterface $message): void
    {
        $updatedItem = $message->getUpdatedItem();

        $loggableContext = $this->loggableContextFactory->create($message->getClassName());
        if (!is_array($loggableContext)) {
            return;
        }

        // the item was removed in the meantime (e.g. a LoggableChildInterface
        // logging to a parent that was deleted in the same flush); Doctrine
        // clears generated identifiers on removal, and an item without an
        // identifier has no current data tracker to compare against
        if (null === $updatedItem->getId()) {
            return;
        }

        $currentDataTracker = $this->currentDataTrackerProvider->getCurrentDataTrackerByClassAndId($message->getClassName(), $updatedItem->getId());

        if (!$currentDataTracker instanceof CurrentDataTrackerInterface) {
            return;
        }

        $this->activityLogProcessor->processUpdatedItem($message, $currentDataTracker);
    }
}
