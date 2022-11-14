<?php

namespace Locastic\ActivityLog\DataProvider;

use Locastic\ActivityLog\Bridge\Elasticsearch\Context\ElasticsearchContextFactoryInterface;
use Locastic\ActivityLog\Bridge\Elasticsearch\ElasticsearchService;
use Locastic\ActivityLog\Model\ActivityLog;
use Locastic\ActivityLog\Model\CurrentDataTracker;

class ActivityLogProvider implements ActivityLogProviderInterface
{
    private ElasticsearchService $elasticsearchService;
    private ElasticsearchContextFactoryInterface $elasticsearchContextFactory;

    public function __construct(ElasticsearchService $elasticsearchService, ElasticsearchContextFactoryInterface $elasticsearchContextFactory)
    {
        $this->elasticsearchService = $elasticsearchService;
        $this->elasticsearchContextFactory = $elasticsearchContextFactory;
    }

    public function getActivityLogsByClass(string $className, array $sort = []): array
    {
        $elasticContext = $this->elasticsearchContextFactory->createFromClassName($className);

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
        $elasticContext = $this->elasticsearchContextFactory->createFromClassName($className);

        $body = [
            'query' => ['term' => ['objectId' => $objectId]]
        ];

        // todo move class to config
        return $this->elasticsearchService->getItemByQuery(
            $elasticContext->getCurrentDataTrackerIndex(),
            CurrentDataTracker::class,
            $body);
    }

    public function getActivityLogsByClassAndId(string $className, $objectId, array $sort = []): array
    {
        $elasticContext = $this->elasticsearchContextFactory->createFromClassName($className);

        $body = [
            'sort' => $sort,
            'query' => [
                'term' => ['objectId' => $objectId],
            ]
        ];

        // todo move class to config
        return $this->elasticsearchService->getCollection(
            $elasticContext->getActivityLogIndex(),
            ActivityLog::class,
            $body);
    }
}
