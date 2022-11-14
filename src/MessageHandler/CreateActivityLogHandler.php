<?php

namespace Locastic\ActivityLog\MessageHandler;

use Locastic\ActivityLog\Logger\ActivityLoggerInterface;
use Locastic\ActivityLog\Message\CreateActivityLogMessage;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class CreateActivityLogHandler implements MessageHandlerInterface
{
    private ActivityLoggerInterface $activityLogger;

    public function __construct(ActivityLoggerInterface $activityLogger)
    {
        $this->activityLogger = $activityLogger;
    }

    public function __invoke(CreateActivityLogMessage $message)
    {
        $this->activityLogger->logCreatedItem($message->getItem(), $message->getActionName());
    }
}
