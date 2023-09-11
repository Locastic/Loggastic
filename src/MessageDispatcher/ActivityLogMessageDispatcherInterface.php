<?php

namespace Locastic\Loggastic\MessageDispatcher;

use Locastic\Loggastic\Message\ActivityLogMessageInterface;

interface ActivityLogMessageDispatcherInterface
{
    public function dispatch(ActivityLogMessageInterface $activityLogMessage, ?string $transportName = null): void;
}
