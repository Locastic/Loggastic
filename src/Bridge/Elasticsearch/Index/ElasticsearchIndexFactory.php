<?php

namespace Locastic\Loggastic\Bridge\Elasticsearch\Index;

use Locastic\Loggastic\Bridge\Elasticsearch\Context\ElasticsearchContextFactoryInterface;
use Locastic\Loggastic\Bridge\Elasticsearch\ElasticsearchClient;

class ElasticsearchIndexFactory implements ElasticsearchIndexFactoryInterface
{
    private ElasticsearchClient $elasticsearchClient;
    private ElasticsearchContextFactoryInterface $elasticsearchContextFactory;
    private ElasticsearchIndexConfigurationInterface $elasticsearchIndexConfiguration;

    public function __construct(ElasticsearchClient $elasticsearchClient, ElasticsearchContextFactoryInterface $elasticsearchContextFactory, ElasticsearchIndexConfigurationInterface $elasticsearchIndexConfiguration)
    {
        $this->elasticsearchClient = $elasticsearchClient;
        $this->elasticsearchContextFactory = $elasticsearchContextFactory;
        $this->elasticsearchIndexConfiguration = $elasticsearchIndexConfiguration;
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
