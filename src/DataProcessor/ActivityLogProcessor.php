<?php

namespace Locastic\Loggastic\DataProcessor;

use Locastic\Loggastic\Factory\ActivityLogInputFactoryInterface;
use Locastic\Loggastic\Factory\CurrentDataTrackerInputFactoryInterface;
use Locastic\Loggastic\Message\CreateActivityLogMessageInterface;
use Locastic\Loggastic\Message\DeleteActivityLogMessageInterface;
use Locastic\Loggastic\Message\UpdateActivityLogMessageInterface;
use Locastic\Loggastic\Metadata\LoggableContext\Factory\LoggableContextFactoryInterface;
use Locastic\Loggastic\Model\Output\CurrentDataTrackerInterface;
use Locastic\Loggastic\Serializer\Traits\NormalizationContextTrait;
use Locastic\Loggastic\Storage\ActivityLogStorageInterface;
use Locastic\Loggastic\Storage\CurrentDataTrackerStorageInterface;
use Locastic\Loggastic\Util\ArraysComparer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ActivityLogProcessor implements ActivityLogProcessorInterface
{
    use NormalizationContextTrait;

    public function __construct(
        private readonly NormalizerInterface $objectNormalizer,
        private readonly ActivityLogStorageInterface $activityLogStorage,
        private readonly CurrentDataTrackerStorageInterface $currentDataTrackerStorage,
        private readonly ActivityLogInputFactoryInterface $activityLogInputFactory,
        private readonly CurrentDataTrackerInputFactoryInterface $currentDataTrackerInputFactory,
        private readonly LoggableContextFactoryInterface $loggableContextFactory,
    ) {
    }

    public function processCreatedItem(CreateActivityLogMessageInterface $message): void
    {
        $loggableContext = $this->loggableContextFactory->create($message->getClassName());

        if (!$loggableContext) {
            return;
        }

        $normalizedItem = $this->objectNormalizer->normalize($message->getItem(), 'activityLog', $this->getNormalizationContext($loggableContext));

        // create log to save full item data for later comparison
        $currentDataTracker = $this->currentDataTrackerInputFactory->create($message->getItem(), $normalizedItem);
        $this->currentDataTrackerStorage->save($currentDataTracker, $message->getClassName());

        // create log for item creation
        $activityLog = $this->activityLogInputFactory->createFromActivityLogMessage($message);
        $this->activityLogStorage->save($activityLog, $message->getClassName());
    }

    public function processUpdatedItem(UpdateActivityLogMessageInterface $message, CurrentDataTrackerInterface $currentDataTracker): void
    {
        $loggableContext = $this->loggableContextFactory->create($message->getClassName());

        if (!$loggableContext) {
            return;
        }

        $updatedData = null !== $message->getUpdatedItem() ?
            $this->objectNormalizer->normalize($message->getUpdatedItem(), 'activityLog', $this->getNormalizationContext($loggableContext)) :
            $message->getNormalizedItem();

        // no loggable fields were updated
        if (empty($updatedData) && !$message->isCreateLogWithoutChanges()) {
            return;
        }

        $changes = ArraysComparer::getCompared($updatedData, $currentDataTracker->getData());

        if (!$changes && !$message->isCreateLogWithoutChanges()) {
            return;
        }

        // create log
        $activityLog = $this->activityLogInputFactory->createFromActivityLogMessage($message, $changes);

        $this->activityLogStorage->save($activityLog, $message->getClassName());

        // update full data log
        $currentDataTrackerInput = $this->currentDataTrackerInputFactory->createFromCurrentDataTracker($currentDataTracker);
        $currentDataTrackerInput->setData(json_encode($updatedData, JSON_THROW_ON_ERROR));

        $this->currentDataTrackerStorage->update($currentDataTracker->getId(), $currentDataTrackerInput, $message->getClassName());
    }

    public function processDeletedItem(DeleteActivityLogMessageInterface $message): void
    {
        $loggableContext = $this->loggableContextFactory->create($message->getClassName());

        if (!$loggableContext) {
            return;
        }

        $activityLog = $this->activityLogInputFactory->createFromActivityLogMessage($message);

        $this->activityLogStorage->save($activityLog, $message->getClassName());
    }
}
