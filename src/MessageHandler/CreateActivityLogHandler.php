<?php

namespace Locastic\Loggastic\MessageHandler;

use Locastic\Loggastic\Loggable\LoggableChildInterface;
use Locastic\Loggastic\DataProcessor\ActivityLogProcessorInterface;
use Locastic\Loggastic\Message\CreateActivityLogMessageInterface;
use Locastic\Loggastic\Message\UpdateActivityLogMessage;
use Locastic\Loggastic\MessageDispatcher\ActivityLogMessageDispatcherInterface;
use Locastic\Loggastic\Metadata\LoggableContext\Factory\LoggableContextFactoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class CreateActivityLogHandler
{
    public function __construct(
        private readonly ActivityLogProcessorInterface $activityLogProcessor,
        private readonly LoggableContextFactoryInterface $loggableContextFactory,
        private readonly ActivityLogMessageDispatcherInterface $activityLogMessageDispatcher
    ) {
    }

    public function __invoke(CreateActivityLogMessageInterface $message): void
    {
        $item = $message->getItem();

        if ($item instanceof LoggableChildInterface && is_object($item->logTo())) {
            $this->activityLogMessageDispatcher->dispatch(new UpdateActivityLogMessage($item->logTo()));
        }

        $loggableContext = $this->loggableContextFactory->create($message->getClassName());

        if (!is_array($loggableContext)) {
            return;
        }

        $this->activityLogProcessor->processCreatedItem($message);
    }
}
