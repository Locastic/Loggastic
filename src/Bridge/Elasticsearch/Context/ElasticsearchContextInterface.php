<?php

namespace Locastic\Loggastic\Bridge\Elasticsearch\Context;

interface ElasticsearchContextInterface
{
    public function getActivityLogIndex(): string;

    public function getCurrentDataTrackerIndex(): string;

    public function getClassName(): string;
}
