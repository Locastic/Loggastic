<?php

namespace Locastic\Loggastic\Factory;

use Locastic\Loggastic\Message\ActivityLogMessageInterface;
use Locastic\Loggastic\Model\ActivityLog;
use Locastic\Loggastic\Model\ActivityLogInterface;
use Locastic\Loggastic\Model\CurrentDataTracker;
use Locastic\Loggastic\Model\CurrentDataTrackerInterface;
use Locastic\Loggastic\Util\ClassUtils;

final class ActivityLogFactory implements ActivityLogFactoryInterface
{
    public function create(): ActivityLogInterface
    {
        return new ActivityLog();
    }

    public function createFromActivityLogMessage(ActivityLogMessageInterface $activityLogMessage, ?array $data = []): ActivityLogInterface
    {
        $activityLog = $this->create();

        $activityLog->setDataChangesFromArray($data);
        $activityLog->setAction($activityLogMessage->getActionName());
        $activityLog->setObjectClass($activityLogMessage->getClassName());
        $activityLog->setObjectId($activityLogMessage->getObjectId());
        $activityLog->setRequestUrl($activityLogMessage->getRequestUrl());
        $activityLog->setUser($activityLogMessage->getUser());

        return $activityLog;
    }

    public function createCurrentDataTracker($item, $normalizedData): CurrentDataTrackerInterface
    {
        $currentDataTracker = new CurrentDataTracker();

        $currentDataTracker->setObjectId($item->getId());
        $currentDataTracker->setDataFromArray($normalizedData);
        $currentDataTracker->setObjectClass(ClassUtils::getClass($item));

        return $currentDataTracker;
    }
}
