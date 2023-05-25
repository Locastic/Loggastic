<?php

namespace Locastic\Loggastic\Bridge\Elasticsearch;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;

class ElasticsearchClient
{
    private string $activityLogElasticHost;

    public function __construct(string $activityLogElasticHost)
    {
        $this->activityLogElasticHost = $activityLogElasticHost;
    }

    public function getClient(): Client
    {
        return ClientBuilder::create()->setHosts([$this->activityLogElasticHost])->build();
    }
}
