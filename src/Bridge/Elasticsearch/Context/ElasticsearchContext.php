<?php

namespace Locastic\Loggastic\Bridge\Elasticsearch\Context;

use Locastic\Loggastic\Exception\IndexNotFoundException;
use Locastic\Loggastic\Model\ActivityLogInterface;
use Locastic\Loggastic\Model\CurrentDataTrackerInterface;

class ElasticsearchContext implements ElasticsearchContextInterface
{
    private string $activityLogIndex;
    private string $currentDataTrackerIndex;
    private string $className;
    private string $shortName;

    public function __construct(string $className, string $shortName, string $activityLogIndex, string $currentDataTrackerIndex)
    {
        $this->activityLogIndex = $activityLogIndex;
        $this->currentDataTrackerIndex = $currentDataTrackerIndex;
        $this->className = $className;
        $this->shortName = $shortName;
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
