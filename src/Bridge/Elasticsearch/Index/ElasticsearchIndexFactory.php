<?php

namespace Locastic\Loggastic\Bridge\Elasticsearch\Index;

use Locastic\Loggastic\Bridge\Elasticsearch\Context\ElasticsearchContextFactoryInterface;
use Locastic\Loggastic\Bridge\Elasticsearch\ElasticsearchClient;

final class ElasticsearchIndexFactory implements ElasticsearchIndexFactoryInterface
{
    public function __construct(private readonly ElasticsearchClient $elasticsearchClient, private readonly ElasticsearchContextFactoryInterface $elasticsearchContextFactory, private readonly ElasticsearchIndexConfigurationInterface $elasticsearchIndexConfiguration)
    {
    }

    public function recreateActivityLogIndex(string $className): void
    {
        $elasticContext = $this->elasticsearchContextFactory->create($className);
        $params = $this->elasticsearchIndexConfiguration->getActivityLogIndexConfig($elasticContext);

        $this->deleteIndex($elasticContext->getActivityLogIndex());
        $this->elasticsearchClient->getClient()->indices()->create($params);
    }

    public function recreateCurrentDataTrackerLogIndex(string $className): void
    {
        $elasticContext = $this->elasticsearchContextFactory->create($className);
        $params = $this->elasticsearchIndexConfiguration->getCurrentDataTrackerIndexConfig($elasticContext);

        $this->deleteIndex($elasticContext->getCurrentDataTrackerIndex());
        $this->elasticsearchClient->getClient()->indices()->create($params);
    }

    public function createActivityLogIndex(string $className): void
    {
        $elasticContext = $this->elasticsearchContextFactory->create($className);

        $params = $this->elasticsearchIndexConfiguration->getActivityLogIndexConfig($elasticContext);

        $this->elasticsearchClient->getClient()->indices()->create($params);
    }

    public function createCurrentDataTrackerLogIndex(string $className): void
    {
        $elasticContext = $this->elasticsearchContextFactory->create($className);

        $params = $this->elasticsearchIndexConfiguration->getCurrentDataTrackerIndexConfig($elasticContext);

        $this->elasticsearchClient->getClient()->indices()->create($params);
    }

    private function deleteIndex(string $index): void
    {
        try {
            $this->elasticsearchClient->getClient()->indices()->delete(['index' => $index]);
        } catch (\Exception $e) {
            // don't throw exception if index doesn't exist
            if (404 !== $e->getCode()) {
                throw $e;
            }
        }
    }
}
