<?php

namespace Locastic\Loggastic\DataProvider;

use Locastic\Loggastic\Bridge\Elasticsearch\Context\ElasticsearchContextFactoryInterface;
use Locastic\Loggastic\Bridge\Elasticsearch\ElasticsearchService;
use Locastic\Loggastic\Model\Output\ActivityLog;
use Locastic\Loggastic\Model\Output\CurrentDataTracker;

final class ActivityLogProvider implements ActivityLogProviderInterface
{
    public function __construct(
        private readonly ElasticsearchService $elasticsearchService,
        private readonly ElasticsearchContextFactoryInterface $elasticsearchContextFactory
    ) { }

    public function getActivityLogsByClass(string $className, array $sort = [], int $limit = 20, int $offset = 0): array
    {
        $elasticContext = $this->elasticsearchContextFactory->create($className);

        $body = [
            'sort' => $sort,
        ];

        // todo move class to config
        return $this->elasticsearchService->getCollection(
            $elasticContext->getActivityLogIndex(),
            ActivityLog::class,
            $body,
            $limit,
            $offset
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

    public function getActivityLogsByClassAndId(
        string $className,
        mixed $objectId,
        array $sort = [],
        int $limit = 20,
        int $offset = 0
    ): array {
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
            $body,
            $limit,
            $offset
        );
    }

    public function getActivityLogsByIndexAndId(
        string $index,
        $objectId,
        array $sort = [],
        int $limit = 20,
        int $offset = 0
    ): array {
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
            $body,
            $limit,
            $offset
        );
    }
}
