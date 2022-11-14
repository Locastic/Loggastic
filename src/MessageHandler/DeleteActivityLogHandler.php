<?php

namespace Locastic\ActivityLog\MessageHandler;

use Locastic\ActivityLog\Logger\ActivityLoggerInterface;
use Locastic\ActivityLog\Message\DeleteActivityLogMessage;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class DeleteActivityLogHandler implements MessageHandlerInterface
{
    private ActivityLoggerInterface $activityLogger;

    public function __construct(ActivityLoggerInterface $activityLogger)
    {
        $this->activityLogger = $activityLogger;
    }

    public function __invoke(DeleteActivityLogMessage $message)
    {
        $this->activityLogger->logDeletedItem($message->getItemId(), $message->getResourceClass(), $message->getActionName());
    }

}
