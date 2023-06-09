<?php

namespace Locastic\Loggastic\Bridge\Elasticsearch\Context;

interface ElasticsearchContextFactoryInterface
{
    public function create(string $className): ElasticsearchContext;
}
