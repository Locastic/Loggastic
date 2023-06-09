<?php

namespace Locastic\Loggastic\DataProvider;

use Locastic\Loggastic\Bridge\Elasticsearch\Context\ElasticsearchContextFactoryInterface;
use Locastic\Loggastic\Bridge\Elasticsearch\ElasticsearchService;
use Locastic\Loggastic\Model\CurrentDataTracker;
use Locastic\Loggastic\Model\CurrentDataTrackerInterface;

class CurrentDataTrackerProvider implements CurrentDataTrackerProviderInterface
{
    public function __construct(private readonly ElasticsearchService $elasticsearchService, private readonly ElasticsearchContextFactoryInterface $elasticsearchContextFactory)
    {
    }

    public function getCurrentDataTrackerByClassAndId(string $className, $objectId): ?CurrentDataTrackerInterface
    {
        $elasticContext = $this->elasticsearchContextFactory->create($className);

        $body = [
            'query' => ['term' => ['objectId' => $objectId]],
        ];

        //todo move class to config
        return $this->elasticsearchService->getItemByQuery(
            $elasticContext->getCurrentDataTrackerIndex(),
            CurrentDataTracker::class,
            $body
        );
    }
}
