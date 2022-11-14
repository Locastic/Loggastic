<?php

namespace Locastic\ActivityLog\Logger;

use Locastic\ActivityLog\Bridge\Elasticsearch\Context\ElasticsearchContextFactoryInterface;
use Locastic\ActivityLog\Bridge\Elasticsearch\ElasticsearchService;
use Locastic\ActivityLog\Factory\ActivityLogFactory;
use Locastic\ActivityLog\Metadata\LoggableContext\Factory\LoggableContextFactoryInterface;
use Locastic\ActivityLog\Model\CurrentDataTracker;
use Locastic\ActivityLog\Util\ArrayDiff;
use Locastic\ActivityLog\Util\ClassUtils;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ActivityLogger implements ActivityLoggerInterface
{
    private NormalizerInterface $objectNormalizer;
    private ElasticsearchService $elasticService;
    private ActivityLogFactory $activityLogFactory;
    private ElasticsearchContextFactoryInterface $elasticsearchContextFactory;
    private LoggableContextFactoryInterface $loggableContextFactory;

    public function __construct(
        ElasticsearchContextFactoryInterface $elasticsearchContextFactory,
        NormalizerInterface $objectNormalizer,
        ElasticsearchService $elasticService,
        ActivityLogFactory $activityLogFactory,
        LoggableContextFactoryInterface $loggableContextFactory
    )
    {
        $this->objectNormalizer = $objectNormalizer;
        $this->elasticService = $elasticService;
        $this->activityLogFactory = $activityLogFactory;
        $this->elasticsearchContextFactory = $elasticsearchContextFactory;
        $this->loggableContextFactory = $loggableContextFactory;
    }

    public function logCreatedItem($item, string $actionName): void
    {
        $loggableClass = ClassUtils::getClass($item);
        $loggableContext = $this->loggableContextFactory->create($loggableClass);

        if(!$loggableContext) {
            return;
        }

        $normalizedItem = $this->objectNormalizer->normalize($item,'array', $this->getNormalizationContext($loggableContext));

        $elasticContext = $this->elasticsearchContextFactory->createFromClassName($loggableClass);

        // create log to save full item data for later comparison
        $currentDataTracker = $this->activityLogFactory->createCurrentDataTracker($item, $normalizedItem);
        $this->elasticService->createItem($currentDataTracker, $elasticContext->getCurrentDataTrackerIndex());

        // create log for item creation
        $activityLog = $this->activityLogFactory->createActivityLog($item->getId(), $loggableClass, $actionName);
        $this->elasticService->createItem($activityLog, $elasticContext->getActivityLogIndex());
    }

    public function logUpdatedItem($updatedItem, CurrentDataTracker $currentDataTracker, string $actionName): void
    {
        $loggableClass = ClassUtils::getClass($updatedItem);
        $loggableContext = $this->loggableContextFactory->create($loggableClass);

        if(!$loggableContext) {
            return;
        }

        $updatedData = $this->objectNormalizer->normalize($updatedItem,'array', $this->getNormalizationContext($loggableContext));

        // no loggable fields were updated
        if(!$updatedData) {
            return;
        }

        $changes = ArrayDiff::arrayDiffRecursive($currentDataTracker->getDataAsArray(), $updatedData);
        $changes2 = ArrayDiff::arrayDiffRecursive($updatedData, $currentDataTracker->getDataAsArray());

        if(!$changes || !$changes2) {
            return;
        }

        $elasticContext = $this->elasticsearchContextFactory->createFromClassName($loggableClass);

        // create log
        $activityLog = $this->activityLogFactory->createActivityLog($updatedItem->getId(), $loggableClass, $actionName, ['previousValues' => $changes, 'currentValues' => $changes2]);
        $this->elasticService->createItem($activityLog, $elasticContext->getActivityLogIndex());

        //update full data log
        $currentDataTracker->setDataFromArray($updatedData);
        $this->elasticService->updateItem($currentDataTracker->getId(), $currentDataTracker, $elasticContext->getCurrentDataTrackerIndex());
    }

    public function logDeletedItem($itemId, string $loggableClass, string $actionName): void
    {
        $loggableContext = $this->loggableContextFactory->create($loggableClass);

        if(!$loggableContext) {
            return;
        }

        $activityLog = $this->activityLogFactory->createActivityLog($itemId, $loggableClass, $actionName);

        $elasticContext = $this->elasticsearchContextFactory->createFromClassName($loggableClass);
        $this->elasticService->createItem($activityLog, $elasticContext->getActivityLogIndex());
    }

     private function getNormalizationContext(array $loggableContext): array
    {
        return [
            'groups' => $loggableContext['groups'],
            DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s',
        ];
    }
}
