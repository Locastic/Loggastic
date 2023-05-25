<?php

namespace Locastic\Loggastic\MessageHandler;

use Locastic\Loggastic\Bridge\Elasticsearch\Context\Traits\ElasticNormalizationContextTrait;
use Locastic\Loggastic\DataProvider\CurrentDataTrackerProviderInterface;
use Locastic\Loggastic\Logger\ActivityLoggerInterface;
use Locastic\Loggastic\Message\UpdateActivityLogMessageInterface;
use Locastic\Loggastic\Metadata\LoggableContext\Factory\LoggableContextFactory;
use Locastic\Loggastic\Model\CurrentDataTracker;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

#[AsMessageHandler]
class UpdateActivityLogHandler implements MessageHandlerInterface
{
    use ElasticNormalizationContextTrait;

    private ActivityLoggerInterface $activityLogger;
    private CurrentDataTrackerProviderInterface $currentDataTrackerProvider;
    private LoggableContextFactory $loggableContextFactory;

    public function __construct(
        ActivityLoggerInterface $activityLogger,
        CurrentDataTrackerProviderInterface $currentDataTrackerProvider,
        LoggableContextFactory $loggableContextFactory
    ) {
        $this->activityLogger = $activityLogger;
        $this->currentDataTrackerProvider = $currentDataTrackerProvider;
        $this->loggableContextFactory = $loggableContextFactory;
    }

    public function __invoke(UpdateActivityLogMessageInterface $message)
    {
        $updatedItem = $message->getUpdatedItem();

        $loggableContext = $this->loggableContextFactory->create($message->getClassName());
        if (!is_array($loggableContext)) {
            return;
        }

        $currentDataTracker = $this->currentDataTrackerProvider->getCurrentDataTrackerByClassAndId($message->getClassName(), $updatedItem->getId());

        if (!$currentDataTracker instanceof CurrentDataTracker) {
            return;
        }

        $this->activityLogger->logUpdatedItem($message, $currentDataTracker);
    }
}
