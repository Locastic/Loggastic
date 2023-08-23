<?php

namespace Locastic\Loggastic\MessageHandler;

use Locastic\Loggastic\Bridge\Elasticsearch\Context\Traits\ElasticNormalizationContextTrait;
use Locastic\Loggastic\DataProvider\CurrentDataTrackerProviderInterface;
use Locastic\Loggastic\DataProcessor\ActivityLogProcessorInterface;
use Locastic\Loggastic\Message\UpdateActivityLogMessageInterface;
use Locastic\Loggastic\Metadata\LoggableContext\Factory\LoggableContextFactory;
use Locastic\Loggastic\Model\Output\CurrentDataTrackerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class UpdateActivityLogHandler
{
    use ElasticNormalizationContextTrait;

    public function __construct(
        private readonly ActivityLogProcessorInterface $activityLogProcessor,
        private readonly CurrentDataTrackerProviderInterface $currentDataTrackerProvider,
        private readonly LoggableContextFactory $loggableContextFactory
    ) {
    }

    public function __invoke(UpdateActivityLogMessageInterface $message): void
    {
        $updatedItem = $message->getUpdatedItem();

        $loggableContext = $this->loggableContextFactory->create($message->getClassName());
        if (!is_array($loggableContext)) {
            return;
        }

        $currentDataTracker = $this->currentDataTrackerProvider->getCurrentDataTrackerByClassAndId($message->getClassName(), $updatedItem->getId());

        if (!$currentDataTracker instanceof CurrentDataTrackerInterface) {
            return;
        }

        $this->activityLogProcessor->processUpdatedItem($message, $currentDataTracker);
    }
}
