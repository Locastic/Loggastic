<?php

namespace Locastic\Loggastic\Bridge\Elasticsearch\Context;

use Locastic\Loggastic\Exception\IndexNotFoundException;
use Locastic\Loggastic\Model\ActivityLogInterface;
use Locastic\Loggastic\Model\CurrentDataTrackerInterface;

class ElasticsearchContext implements ElasticsearchContextInterface
{
    public function __construct(private readonly string $className, private readonly string $shortName, private readonly string $activityLogIndex, private readonly string $currentDataTrackerIndex)
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

    public function getShortName(): string
    {
        return $this->shortName;
    }
}
