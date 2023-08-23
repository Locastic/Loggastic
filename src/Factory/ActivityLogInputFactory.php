<?php

namespace Locastic\Loggastic\Factory;

use Locastic\Loggastic\Message\ActivityLogMessageInterface;
use Locastic\Loggastic\Model\Input\ActivityLogInput;
use Locastic\Loggastic\Model\Input\ActivityLogInputInterface;

final class ActivityLogInputFactory implements ActivityLogInputFactoryInterface
{
    public function createFromActivityLogMessage(ActivityLogMessageInterface $activityLogMessage, ?array $dataChanges = []): ActivityLogInputInterface
    {
        $activityLog = new ActivityLogInput();

        $activityLog->setDataChanges(json_encode($dataChanges, JSON_THROW_ON_ERROR));
        $activityLog->setAction($activityLogMessage->getActionName());
        $activityLog->setObjectClass($activityLogMessage->getClassName());
        $activityLog->setObjectId($activityLogMessage->getObjectId());
        $activityLog->setRequestUrl($activityLogMessage->getRequestUrl());
        $activityLog->setUser($activityLogMessage->getUser());

        return $activityLog;
    }
}
