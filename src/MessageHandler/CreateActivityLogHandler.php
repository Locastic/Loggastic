<?php

namespace Locastic\Loggastic\MessageHandler;

use Locastic\Loggastic\Loggable\LoggableChildInterface;
use Locastic\Loggastic\Logger\ActivityLoggerInterface;
use Locastic\Loggastic\Message\CreateActivityLogMessageInterface;
use Locastic\Loggastic\Message\UpdateActivityLogMessage;
use Locastic\Loggastic\Metadata\LoggableContext\Factory\LoggableContextFactoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class CreateActivityLogHandler implements MessageHandlerInterface
{
    private ActivityLoggerInterface $activityLogger;
    private LoggableContextFactoryInterface $loggableContextFactory;
    private MessageBusInterface $bus;

    public function __construct(
        ActivityLoggerInterface $activityLogger,
        LoggableContextFactoryInterface $loggableContextFactory,
        MessageBusInterface $bus
    ) {
        $this->activityLogger = $activityLogger;
        $this->loggableContextFactory = $loggableContextFactory;
        $this->bus = $bus;
    }

    public function __invoke(CreateActivityLogMessageInterface $message)
    {
        $item = $message->getItem();

        if ($item instanceof LoggableChildInterface && is_object($item->logTo())) {
            $this->bus->dispatch(new UpdateActivityLogMessage($item->logTo()));
        }

        $loggableContext = $this->loggableContextFactory->create($message->getClassName());

        if (!is_array($loggableContext)) {
            return;
        }

        $this->activityLogger->logCreatedItem($message);
    }
}
