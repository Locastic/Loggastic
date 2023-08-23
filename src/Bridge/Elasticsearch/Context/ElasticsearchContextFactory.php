<?php

namespace Locastic\Loggastic\Bridge\Elasticsearch\Context;

use Locastic\Loggastic\Model\Output\ActivityLog;
use Locastic\Loggastic\Model\Output\CurrentDataTracker;
use Locastic\Loggastic\Util\StringConverter;

final class ElasticsearchContextFactory implements ElasticsearchContextFactoryInterface
{
    public function create(string $className): ElasticsearchContext
    {
        $activityLogIndex = $this->getIndexName($className).'_'.$this->getIndexName(ActivityLog::class);
        $currentDataTrackerIndex = $this->getIndexName($className).'_'.$this->getIndexName(CurrentDataTracker::class);

        return new ElasticsearchContext($className, $activityLogIndex, $currentDataTrackerIndex);
    }

    private function getIndexName(string $className): string
    {
        $reflectionClass = new \ReflectionClass($className);

        return StringConverter::tableize($reflectionClass->getShortName());
    }
}
