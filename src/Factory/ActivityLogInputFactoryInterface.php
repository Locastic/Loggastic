<?php

namespace Locastic\Loggastic\Factory;

use Locastic\Loggastic\Message\ActivityLogMessageInterface;
use Locastic\Loggastic\Model\Input\ActivityLogInputInterface;

interface ActivityLogInputFactoryInterface
{
    public function createFromActivityLogMessage(ActivityLogMessageInterface $activityLogMessage, ?array $dataChanges = null): ActivityLogInputInterface;
}
