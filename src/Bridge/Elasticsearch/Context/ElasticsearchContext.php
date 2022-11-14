<?php

namespace Locastic\ActivityLog\Bridge\Elasticsearch\Context;

use Locastic\ActivityLog\Exception\IndexNotFoundException;
use Locastic\ActivityLog\Model\ActivityLogInterface;
use Locastic\ActivityLog\Model\CurrentDataTrackerInterface;
use Locastic\ActivityLog\Model\LogInterface;
use Locastic\ActivityLog\Util\ClassUtils;

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

    public function getIndexByClass(string $className): string
    {
        if($className instanceof ActivityLogInterface) {
            return $this->activityLogIndex;
        }

        if($className instanceof CurrentDataTrackerInterface) {
            return $this->currentDataTrackerIndex;
        }

        throw new IndexNotFoundException();
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
