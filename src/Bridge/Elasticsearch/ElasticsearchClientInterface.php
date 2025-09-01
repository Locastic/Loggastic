<?php

namespace Locastic\Loggastic\Bridge\Elasticsearch;

use Elasticsearch\Client;

interface ElasticsearchClientInterface
{
    public function getClient(): Client;
}
