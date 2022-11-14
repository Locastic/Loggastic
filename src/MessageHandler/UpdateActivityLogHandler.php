<?php

namespace Locastic\ActivityLog\MessageHandler;

use Locastic\ActivityLog\DataProvider\CurrentDataTrackerProviderInterface;
use Locastic\ActivityLog\Logger\ActivityLoggerInterface;
use Locastic\ActivityLog\Metadata\LoggableContext\Factory\LoggableContextFactoryInterface;
use Locastic\ActivityLog\Model\CurrentDataTracker;
use Locastic\ActivityLog\Message\UpdateActivityLogMessage;

use Locastic\ActivityLog\Util\ClassUtils;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class UpdateActivityLogHandler implements MessageHandlerInterface
{
    private ActivityLoggerInterface $activityLogger;
    private CurrentDataTrackerProviderInterface $currentDataTrackerProvider;

    public function __construct(ActivityLoggerInterface $activityLogger, CurrentDataTrackerProviderInterface $currentDataTrackerProvider)
    {
        $this->activityLogger = $activityLogger;
        $this->currentDataTrackerProvider = $currentDataTrackerProvider;
    }

    public function __invoke(UpdateActivityLogMessage $message)
    {
        $updatedItem = $message->getUpdatedItem();
        $resourceClass = ClassUtils::getClass($updatedItem);

        $currentDataTracker = $this->currentDataTrackerProvider->getCurrentDataTrackerByClassAndId($resourceClass, $updatedItem->getId());

        if(!$currentDataTracker instanceof CurrentDataTracker) {
            return;
        }

        $this->activityLogger->logUpdatedItem($updatedItem, $currentDataTracker, $message->getActionName());
    }
}
