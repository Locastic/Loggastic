<?php

namespace Locastic\Loggastic\Bridge\Elasticsearch\Index;

use Locastic\Loggastic\Bridge\Elasticsearch\Context\ElasticsearchContextInterface;

interface ElasticsearchIndexConfigurationInterface
{
    public function getActivityLogIndexConfig(ElasticsearchContextInterface $elasticsearchContext): array;

    public function getCurrentDataTrackerIndexConfig(ElasticsearchContextInterface $elasticsearchContext): array;
}
