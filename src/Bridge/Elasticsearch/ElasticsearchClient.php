<?php

namespace Locastic\Loggastic\Bridge\Elasticsearch;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;

final class ElasticsearchClient
{
    public function __construct(private readonly string $activityLogElasticHost)
    {
    }

    public function getClient(): Client
    {
        return ClientBuilder::create()->setHosts([$this->activityLogElasticHost])->build();
    }
}
