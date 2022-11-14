<?php

namespace Locastic\ActivityLog\Bridge\Elasticsearch\Index;

use Locastic\ActivityLog\Bridge\Elasticsearch\Context\ElasticsearchContextInterface;

interface ElasticsearchIndexConfigurationInterface
{
    public function getActivityLogIndexConfig(ElasticsearchContextInterface $elasticsearchContext): array;
    public function getCurrentDataTrackerIndexConfig(ElasticsearchContextInterface $elasticsearchContext): array;
}
