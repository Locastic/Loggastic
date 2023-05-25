<?php

namespace Locastic\Loggastic\Bridge\Elasticsearch\Context;

use Locastic\Loggastic\Model\ActivityLog;
use Locastic\Loggastic\Model\CurrentDataTracker;
use Locastic\Loggastic\Util\StringConverter;

class ElasticsearchContextFactory implements ElasticsearchContextFactoryInterface
{
    public function create(string $className): ElasticsearchContext
    {
        $reflectionClass = new \ReflectionClass($className);

        $activityLogIndex = $this->getIndexName($className).'_'.$this->getIndexName(ActivityLog::class);
        $currentDataTrackerIndex = $this->getIndexName($className).'_'.$this->getIndexName(CurrentDataTracker::class);

        return new ElasticsearchContext($className, $reflectionClass->getShortName(), $activityLogIndex, $currentDataTrackerIndex);
    }

    private function getIndexName(string $className): string
    {
        $reflectionClass = new \ReflectionClass($className);

        return StringConverter::tableize($reflectionClass->getShortName());
    }
}
