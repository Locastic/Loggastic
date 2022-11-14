<?php

namespace Locastic\ActivityLog\Bridge\Elasticsearch\Context;

interface ElasticsearchContextInterface
{
    public function getActivityLogIndex(): string;

    public function getCurrentDataTrackerIndex(): string;

    public function getClassName(): string;

    public function getShortName(): string;
}
