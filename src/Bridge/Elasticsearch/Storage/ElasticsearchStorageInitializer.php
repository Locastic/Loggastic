<?php

namespace Locastic\Loggastic\Bridge\Elasticsearch\Storage;

use Elastic\Elasticsearch\Exception\ClientResponseException;
use Locastic\Loggastic\Bridge\Elasticsearch\Index\ElasticsearchIndexFactoryInterface;
use Locastic\Loggastic\Storage\StorageInitializerInterface;

final class ElasticsearchStorageInitializer implements StorageInitializerInterface
{
    public function __construct(private readonly ElasticsearchIndexFactoryInterface $elasticsearchIndexFactory)
    {
    }

    public function initializeActivityLogStorage(string $className): bool
    {
        try {
            $this->elasticsearchIndexFactory->createActivityLogIndex($className);
        } catch (ClientResponseException $e) {
            if (!str_contains($e->getMessage(), 'resource_already_exists_exception')) {
                throw $e;
            }

            return false;
        }

        return true;
    }

    public function initializeCurrentDataTrackerStorage(string $className): bool
    {
        try {
            $this->elasticsearchIndexFactory->createCurrentDataTrackerLogIndex($className);
        } catch (ClientResponseException $e) {
            if (!str_contains($e->getMessage(), 'resource_already_exists_exception')) {
                throw $e;
            }

            return false;
        }

        return true;
    }

    public function recreateActivityLogStorage(string $className): void
    {
        $this->elasticsearchIndexFactory->recreateActivityLogIndex($className);
    }

    public function recreateCurrentDataTrackerStorage(string $className): void
    {
        $this->elasticsearchIndexFactory->recreateCurrentDataTrackerLogIndex($className);
    }
}
