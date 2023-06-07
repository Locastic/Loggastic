<?php

namespace Locastic\Loggastic\DataProcessor;

use Locastic\Loggastic\Bridge\Elasticsearch\Context\ElasticsearchContextFactoryInterface;
use Locastic\Loggastic\Bridge\Elasticsearch\Context\Traits\ElasticNormalizationContextTrait;
use Locastic\Loggastic\Bridge\Elasticsearch\ElasticsearchService;
use Locastic\Loggastic\Factory\ActivityLogFactoryInterface;
use Locastic\Loggastic\Message\CreateActivityLogMessageInterface;
use Locastic\Loggastic\Message\DeleteActivityLogMessageInterface;
use Locastic\Loggastic\Message\UpdateActivityLogMessageInterface;
use Locastic\Loggastic\Metadata\LoggableContext\Factory\LoggableContextFactoryInterface;
use Locastic\Loggastic\Model\CurrentDataTracker;
use Locastic\Loggastic\Util\ArraysComparer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ActivityLogProcessor implements ActivityLogProcessorInterface
{
    use ElasticNormalizationContextTrait;

    private NormalizerInterface $objectNormalizer;
    private ElasticsearchService $elasticService;
    private ActivityLogFactoryInterface $activityLogFactory;
    private ElasticsearchContextFactoryInterface $elasticsearchContextFactory;
    private LoggableContextFactoryInterface $loggableContextFactory;

    public function __construct(
        ElasticsearchContextFactoryInterface $elasticsearchContextFactory,
        NormalizerInterface $objectNormalizer,
        ElasticsearchService $elasticService,
        ActivityLogFactoryInterface $activityLogFactory,
        LoggableContextFactoryInterface $loggableContextFactory
    ) {
        $this->objectNormalizer = $objectNormalizer;
        $this->elasticService = $elasticService;
        $this->activityLogFactory = $activityLogFactory;
        $this->elasticsearchContextFactory = $elasticsearchContextFactory;
        $this->loggableContextFactory = $loggableContextFactory;
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
        $currentDataTracker = $this->activityLogFactory->createCurrentDataTracker($message->getItem(), $normalizedItem);
        $this->elasticService->createItem($currentDataTracker, $elasticContext->getCurrentDataTrackerIndex(), ['current_data_tracker']);

        // create log for item creation
        $activityLog = $this->activityLogFactory->createFromActivityLogMessage($message);
        $this->elasticService->createItem($activityLog, $elasticContext->getActivityLogIndex(), ['activity_log']);
    }

    public function processUpdatedItem(UpdateActivityLogMessageInterface $message, CurrentDataTracker $currentDataTracker): void
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

        $changes = ArraysComparer::getCompared($updatedData, $currentDataTracker->getDataAsArray());

        if (!$changes && !$message->isCreateLogWithoutChanges()) {
            dd('no changes');
            return;
        }

        $elasticContext = $this->elasticsearchContextFactory->create($message->getClassName());

        // create log
        $activityLog = $this->activityLogFactory->createFromActivityLogMessage($message, $changes);

        $this->elasticService->createItem($activityLog, $elasticContext->getActivityLogIndex(), ['activity_log']);

        //update full data log
        $currentDataTracker->setDataFromArray($updatedData);
        $this->elasticService->updateItem($currentDataTracker->getId(), $currentDataTracker, $elasticContext->getCurrentDataTrackerIndex(), ['current_data_tracker']);
    }

    public function processDeletedItem(DeleteActivityLogMessageInterface $message): void
    {
        $loggableContext = $this->loggableContextFactory->create($message->getClassName());

        if (!$loggableContext) {
            return;
        }

        $activityLog = $this->activityLogFactory->createFromActivityLogMessage($message);

        $elasticContext = $this->elasticsearchContextFactory->create($message->getClassName());
        $this->elasticService->createItem($activityLog, $elasticContext->getActivityLogIndex(), ['activity_log']);
    }
}
