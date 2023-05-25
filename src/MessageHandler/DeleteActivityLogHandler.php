<?php

namespace Locastic\Loggastic\MessageHandler;

use Locastic\Loggastic\Logger\ActivityLoggerInterface;
use Locastic\Loggastic\Message\DeleteActivityLogMessageInterface;
use Locastic\Loggastic\Metadata\LoggableContext\Factory\LoggableContextFactoryInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class DeleteActivityLogHandler implements MessageHandlerInterface
{
    private ActivityLoggerInterface $activityLogger;
    private LoggableContextFactoryInterface $loggableContextFactory;

    public function __construct(
        ActivityLoggerInterface $activityLogger,
        LoggableContextFactoryInterface $loggableContextFactory
    ) {
        $this->activityLogger = $activityLogger;
        $this->loggableContextFactory = $loggableContextFactory;
    }

    public function __invoke(DeleteActivityLogMessageInterface $message)
    {
        $loggableContext = $this->loggableContextFactory->create($message->getClassName());
        if (!is_array($loggableContext)) {
            return;
        }

        $this->activityLogger->logDeletedItem($message);
    }
}
