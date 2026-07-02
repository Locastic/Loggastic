<?php

namespace Locastic\Loggastic\Bridge\Elasticsearch;

use Elastic\Elasticsearch\Client;

interface ElasticsearchClientInterface
{
    public function getClient(): Client;
}
