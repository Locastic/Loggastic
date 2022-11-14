<?php

namespace Locastic\ActivityLog\DataProvider;

use Locastic\ActivityLog\Bridge\Elasticsearch\Context\ElasticsearchContextFactoryInterface;
use Locastic\ActivityLog\Bridge\Elasticsearch\ElasticsearchService;
use Locastic\ActivityLog\Model\CurrentDataTracker;
use Locastic\ActivityLog\Model\CurrentDataTrackerInterface;

class CurrentDataTrackerProvider implements CurrentDataTrackerProviderInterface
{
    private ElasticsearchService $elasticsearchService;
    private ElasticsearchContextFactoryInterface $elasticsearchContextFactory;

    public function __construct(ElasticsearchService $elasticsearchService, ElasticsearchContextFactoryInterface $elasticsearchContextFactory)
    {
        $this->elasticsearchService = $elasticsearchService;
        $this->elasticsearchContextFactory = $elasticsearchContextFactory;
    }

    public function getCurrentDataTrackerByClassAndId(string $className, $objectId): ?CurrentDataTrackerInterface
    {
        $elasticContext = $this->elasticsearchContextFactory->createFromClassName($className);

        $body = [
            'query' => ['term' => ["objectId" => $objectId]]
        ];

        //todo move class to config
        return $this->elasticsearchService->getItemByQuery(
            $elasticContext->getCurrentDataTrackerIndex(),
            CurrentDataTracker::class,
            $body);
    }
}
