<?php

namespace Locastic\Loggastic\Bridge\Elasticsearch\Context;

class ElasticsearchContext implements ElasticsearchContextInterface
{
    public function __construct(private readonly string $className, private readonly string $activityLogIndex, private readonly string $currentDataTrackerIndex)
    {
    }

    public function getActivityLogIndex(): string
    {
        return $this->activityLogIndex;
    }

    public function getCurrentDataTrackerIndex(): string
    {
        return $this->currentDataTrackerIndex;
    }

    public function getClassName(): string
    {
        return $this->className;
    }
}
