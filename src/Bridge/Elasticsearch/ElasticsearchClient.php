<?php

namespace Locastic\Loggastic\Bridge\Elasticsearch;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;

class ElasticsearchClient
{
    public function __construct(private readonly string $activityLogElasticHost)
    {
    }

    public function getClient(): Client
    {
        return ClientBuilder::create()->setHosts([$this->activityLogElasticHost])->build();
    }
}
