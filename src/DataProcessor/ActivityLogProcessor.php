<?php

namespace Locastic\Loggastic\DataProcessor;

use Locastic\Loggastic\Bridge\Elasticsearch\Context\ElasticsearchContextFactoryInterface;
use Locastic\Loggastic\Bridge\Elasticsearch\Context\Traits\ElasticNormalizationContextTrait;
use Locastic\Loggastic\Bridge\Elasticsearch\ElasticsearchService;
use Locastic\Loggastic\Factory\ActivityLogInputFactoryInterface;
use Locastic\Loggastic\Factory\CurrentDataTrackerInputFactoryInterface;
use Locastic\Loggastic\Message\CreateActivityLogMessageInterface;
use Locastic\Loggastic\Message\DeleteActivityLogMessageInterface;
use Locastic\Loggastic\Message\UpdateActivityLogMessageInterface;
use Locastic\Loggastic\Metadata\LoggableContext\Factory\LoggableContextFactoryInterface;
use Locastic\Loggastic\Model\Output\CurrentDataTrackerInterface;
use Locastic\Loggastic\Util\ArraysComparer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ActivityLogProcessor implements ActivityLogProcessorInterface
{
    use ElasticNormalizationContextTrait;

    public function __construct(
        private readonly ElasticsearchContextFactoryInterface $elasticsearchContextFactory,
        private readonly NormalizerInterface $objectNormalizer,
        private readonly ElasticsearchService $elasticService,
        private readonly ActivityLogInputFactoryInterface $activityLogInputFactory,
        private readonly CurrentDataTrackerInputFactoryInterface $currentDataTrackerInputFactory,
        private readonly LoggableContextFactoryInterface $loggableContextFactory
    ) {
    }

    public function processCreatedItem(CreateActivityLogMessageInterface $message): void
    {
        $loggableContext = $this->loggableContextFactory->create($message->getClassName());

        if (!$loggableContext) {
            return;
        }

        $normalizedItem = $this->objectNormalizer->normalize($message->getItem(), 'activityLog', $this->getNormalizationContext($loggableContext));

        $elasticContext = $this->elasticsearchContextFactory->create($message->getClassName());

        // create log to save full item data for later comparison
        $currentDataTracker = $this->currentDataTrackerInputFactory->create($message->getItem(), $normalizedItem);
        $this->elasticService->createItem($currentDataTracker, $elasticContext->getCurrentDataTrackerIndex(), ['current_data_tracker']);

        // create log for item creation
        $activityLog = $this->activityLogInputFactory->createFromActivityLogMessage($message);
        $this->elasticService->createItem($activityLog, $elasticContext->getActivityLogIndex(), ['activity_log']);
    }

    public function processUpdatedItem(UpdateActivityLogMessageInterface $message, CurrentDataTrackerInterface $currentDataTracker): void
    {
        $loggableContext = $this->loggableContextFactory->create($message->getClassName());

        if (!$loggableContext) {
            return;
        }

        $updatedData = $message->getNormalizedItem();

        // no loggable fields were updated
        if (!$updatedData && !$message->isCreateLogWithoutChanges()) {
            return;
        }

        $changes = ArraysComparer::getCompared($updatedData, $currentDataTracker->getData());

        if (!$changes && !$message->isCreateLogWithoutChanges()) {
            return;
        }

        $elasticContext = $this->elasticsearchContextFactory->create($message->getClassName());

        // create log
        $activityLog = $this->activityLogInputFactory->createFromActivityLogMessage($message, $changes);

        $this->elasticService->createItem($activityLog, $elasticContext->getActivityLogIndex(), ['activity_log']);

        //update full data log
        $currentDataTrackerInput = $this->currentDataTrackerInputFactory->createFromCurrentDataTracker($currentDataTracker);
        $currentDataTrackerInput->setData(json_encode($updatedData, JSON_THROW_ON_ERROR));

        $this->elasticService->updateItem($currentDataTracker->getId(), $currentDataTrackerInput, $elasticContext->getCurrentDataTrackerIndex(), ['current_data_tracker']);
    }

    public function processDeletedItem(DeleteActivityLogMessageInterface $message): void
    {
        $loggableContext = $this->loggableContextFactory->create($message->getClassName());

        if (!$loggableContext) {
            return;
        }

        $activityLog = $this->activityLogInputFactory->createFromActivityLogMessage($message);

        $elasticContext = $this->elasticsearchContextFactory->create($message->getClassName());
        $this->elasticService->createItem($activityLog, $elasticContext->getActivityLogIndex(), ['activity_log']);
    }
}
