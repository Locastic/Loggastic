<?php

namespace Locastic\Loggastic\DataProvider;

use Locastic\Loggastic\Bridge\Elasticsearch\Context\ElasticsearchContextFactoryInterface;
use Locastic\Loggastic\Bridge\Elasticsearch\ElasticsearchService;
use Locastic\Loggastic\Model\Output\CurrentDataTracker;
use Locastic\Loggastic\Model\Output\CurrentDataTrackerInterface;
use Symfony\Component\Uid\Uuid;

final class CurrentDataTrackerProvider implements CurrentDataTrackerProviderInterface
{
    public function __construct(private readonly ElasticsearchService $elasticsearchService, private readonly ElasticsearchContextFactoryInterface $elasticsearchContextFactory)
    {
    }

    public function getCurrentDataTrackerByClassAndId(string $className, $objectId): ?CurrentDataTrackerInterface
    {
        $elasticContext = $this->elasticsearchContextFactory->create($className);

        $queryTerm = 'term';
        if (class_exists(Uuid::class) && $objectId instanceof Uuid) {
            $queryTerm = 'match_phrase';
        }

        $body = [
            'query' => [$queryTerm => ['objectId' => $objectId]],
        ];

        //todo move class to config
        return $this->elasticsearchService->getItemByQuery(
            $elasticContext->getCurrentDataTrackerIndex(),
            CurrentDataTracker::class,
            $body
        );
    }
}
