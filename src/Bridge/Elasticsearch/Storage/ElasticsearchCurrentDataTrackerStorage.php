<?php

namespace Locastic\Loggastic\Bridge\Elasticsearch\Storage;

use Locastic\Loggastic\Bridge\Elasticsearch\Context\ElasticsearchContextFactoryInterface;
use Locastic\Loggastic\Bridge\Elasticsearch\ElasticsearchServiceInterface;
use Locastic\Loggastic\Model\Input\CurrentDataTrackerInputInterface;
use Locastic\Loggastic\Model\Output\CurrentDataTracker;
use Locastic\Loggastic\Model\Output\CurrentDataTrackerInterface;
use Locastic\Loggastic\Storage\CurrentDataTrackerStorageInterface;

final class ElasticsearchCurrentDataTrackerStorage implements CurrentDataTrackerStorageInterface
{
    public function __construct(
        private readonly ElasticsearchServiceInterface $elasticsearchService,
        private readonly ElasticsearchContextFactoryInterface $elasticsearchContextFactory,
    ) {
    }

    public function save(CurrentDataTrackerInputInterface $currentDataTracker, string $className): void
    {
        $elasticContext = $this->elasticsearchContextFactory->create($className);

        $this->elasticsearchService->createItem($currentDataTracker, $elasticContext->getCurrentDataTrackerIndex(), ['current_data_tracker']);
    }

    public function update(mixed $id, CurrentDataTrackerInputInterface $currentDataTracker, string $className): void
    {
        $elasticContext = $this->elasticsearchContextFactory->create($className);

        $this->elasticsearchService->updateItem($id, $currentDataTracker, $elasticContext->getCurrentDataTrackerIndex(), ['current_data_tracker']);
    }

    /**
     * @param array<int, CurrentDataTrackerInputInterface> $currentDataTrackers
     */
    public function bulkSave(array $currentDataTrackers, string $className): void
    {
        $elasticContext = $this->elasticsearchContextFactory->create($className);

        $this->elasticsearchService->bulkCreate($currentDataTrackers, $elasticContext->getCurrentDataTrackerIndex(), ['current_data_tracker']);
    }

    public function findByClassAndObjectId(string $className, mixed $objectId): ?CurrentDataTrackerInterface
    {
        $elasticContext = $this->elasticsearchContextFactory->create($className);

        $body = [
            'query' => ['term' => ['objectId' => $objectId]],
        ];

        // todo move class to config
        return $this->elasticsearchService->getItemByQuery(
            $elasticContext->getCurrentDataTrackerIndex(),
            CurrentDataTracker::class,
            $body
        );
    }
}
