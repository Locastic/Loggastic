<?php

namespace Locastic\ActivityLog\Bridge\Elasticsearch\Context;

interface ElasticsearchContextFactoryInterface
{
    public function createFromClassName(string $className): ElasticsearchContext;
}

