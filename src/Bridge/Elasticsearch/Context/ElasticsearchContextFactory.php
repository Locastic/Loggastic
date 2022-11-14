<?php

namespace Locastic\ActivityLog\Bridge\Elasticsearch\Context;

use Locastic\ActivityLog\Model\ActivityLog;
use Locastic\ActivityLog\Model\CurrentDataTracker;
use Locastic\ActivityLog\Util\StringConverter;

class ElasticsearchContextFactory implements ElasticsearchContextFactoryInterface
{
    public function createFromClassName(string $className): ElasticsearchContext
    {
        $reflectionClass = new \ReflectionClass($className);

        $activityLogIndex = $this->getIndexName($className). '_' . $this->getIndexName(ActivityLog::class);
        $currentDataTrackerIndex = $this->getIndexName($className). '_' . $this->getIndexName(CurrentDataTracker::class);

        return new ElasticsearchContext($className, $reflectionClass->getShortName(), $activityLogIndex, $currentDataTrackerIndex);
    }

    private function getIndexName(string $className): string
    {
        $reflectionClass = new \ReflectionClass($className);

        return StringConverter::tableize($reflectionClass->getShortName());
    }
}
