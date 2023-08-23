<?php

namespace Locastic\Loggastic\DataProvider;

use Locastic\Loggastic\Bridge\Elasticsearch\Context\ElasticsearchContextFactoryInterface;
use Locastic\Loggastic\Bridge\Elasticsearch\ElasticsearchService;
use Locastic\Loggastic\Model\Output\ActivityLog;
use Locastic\Loggastic\Model\Output\CurrentDataTracker;

class ActivityLogProvider implements ActivityLogProviderInterface
{
    public function __construct(private readonly ElasticsearchService $elasticsearchService, private readonly ElasticsearchContextFactoryInterface $elasticsearchContextFactory)
    {
    }

    public function getActivityLogsByClass(string $className, array $sort = []): array
    {
        $elasticContext = $this->elasticsearchContextFactory->create($className);

        $body = [
            'sort' => $sort,
        ];

        // todo move class to config
        return $this->elasticsearchService->getCollection(
            $elasticContext->getActivityLogIndex(),
            ActivityLog::class,
            $body
        );
    }

    public function getCurrentDataTrackerByClassAndId(string $className, $objectId): ?array
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

    public function getActivityLogsByClassAndId(string $className, mixed $objectId, array $sort = []): array
    {
        $elasticContext = $this->elasticsearchContextFactory->create($className);

        $body = [
            'sort' => $sort,
            'query' => [
                'term' => ['objectId' => $objectId],
            ],
        ];

        // todo move class to config
        return $this->elasticsearchService->getCollection(
            $elasticContext->getActivityLogIndex(),
            ActivityLog::class,
            $body
        );
    }

    public function getActivityLogsByIndexAndId(string $index, $objectId, array $sort = []): array
    {
        $body = [
            'sort' => $sort,
            'query' => [
                'term' => ['objectId' => $objectId],
            ],
        ];

        // todo move class to config
        return $this->elasticsearchService->getCollection(
            $index,
            ActivityLog::class,
            $body
        );
    }
}
