<?php

namespace Locastic\Loggastic\MessageHandler;

use Locastic\Loggastic\DataProcessor\ActivityLogProcessorInterface;
use Locastic\Loggastic\Message\DeleteActivityLogMessageInterface;
use Locastic\Loggastic\Metadata\LoggableContext\Factory\LoggableContextFactoryInterface;

class DeleteActivityLogHandler
{
    public function __construct(
        private readonly ActivityLogProcessorInterface $activityLogProcessor,
        private readonly LoggableContextFactoryInterface $loggableContextFactory
    ) {
    }

    public function __invoke(DeleteActivityLogMessageInterface $message)
    {
        $loggableContext = $this->loggableContextFactory->create($message->getClassName());
        if (!is_array($loggableContext)) {
            return;
        }

        $this->activityLogProcessor->processDeletedItem($message);
    }
}
