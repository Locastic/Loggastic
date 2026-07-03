<?php

namespace Locastic\Loggastic\Bridge\Elasticsearch\Storage;

use Locastic\Loggastic\Bridge\Elasticsearch\Context\ElasticsearchContextFactoryInterface;
use Locastic\Loggastic\Bridge\Elasticsearch\ElasticsearchServiceInterface;
use Locastic\Loggastic\Model\Input\ActivityLogInputInterface;
use Locastic\Loggastic\Model\Output\ActivityLog;
use Locastic\Loggastic\Model\Output\ActivityLogInterface;
use Locastic\Loggastic\Storage\ActivityLogStorageInterface;

final class ElasticsearchActivityLogStorage implements ActivityLogStorageInterface
{
    public function __construct(
        private readonly ElasticsearchServiceInterface $elasticsearchService,
        private readonly ElasticsearchContextFactoryInterface $elasticsearchContextFactory,
    ) {
    }

    public function save(ActivityLogInputInterface $activityLog, string $className): void
    {
        $elasticContext = $this->elasticsearchContextFactory->create($className);

        $this->elasticsearchService->createItem($activityLog, $elasticContext->getActivityLogIndex(), ['activity_log']);
    }

    /**
     * @param array<string, string> $sort
     *
     * @return array<int, ActivityLogInterface>
     */
    public function findByClass(string $className, array $sort = [], int $limit = 20, int $offset = 0): array
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

    /**
     * @param array<string, string> $sort
     *
     * @return array<int, ActivityLogInterface>
     */
    public function findByClassAndObjectId(string $className, mixed $objectId, array $sort = [], int $limit = 20, int $offset = 0): array
    {
        $elasticContext = $this->elasticsearchContextFactory->create($className);

        return $this->findByIndexAndObjectId($elasticContext->getActivityLogIndex(), $objectId, $sort, $limit, $offset);
    }

    /**
     * Elasticsearch-specific: reads activity logs directly from an index, bypassing
     * the class name to index resolution.
     *
     * @param array<string, string> $sort
     *
     * @return array<int, ActivityLogInterface>
     */
    public function findByIndexAndObjectId(string $index, mixed $objectId, array $sort = [], int $limit = 20, int $offset = 0): array
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
            $body,
            $limit,
            $offset
        );
    }
}
