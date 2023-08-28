<?php

namespace Locastic\Loggastic\Logger;

use Locastic\Loggastic\Bridge\Elasticsearch\Context\Traits\ElasticNormalizationContextTrait;
use Locastic\Loggastic\Event\PreDispatchActivityLogMessageEvent;
use Locastic\Loggastic\Loggable\LoggableChildInterface;
use Locastic\Loggastic\Message\CreateActivityLogMessage;
use Locastic\Loggastic\Message\DeleteActivityLogMessage;
use Locastic\Loggastic\Message\UpdateActivityLogMessage;
use Locastic\Loggastic\Message\UpdateActivityLogMessageInterface;
use Locastic\Loggastic\Metadata\LoggableContext\Factory\LoggableContextFactoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ActivityLogger implements ActivityLoggerInterface
{
    use ElasticNormalizationContextTrait;

    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly LoggableContextFactoryInterface $loggableContextFactory,
        private readonly NormalizerInterface $normalizer,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }
    public function logCreatedItem(object $item, ?string $actionName = null): void
    {
        $message = new CreateActivityLogMessage($item, $actionName);

        $this->eventDispatcher->dispatch(PreDispatchActivityLogMessageEvent::create($message));

        $this->bus->dispatch(new Envelope($message));
    }

    public function logDeletedItem($objectId, string $className, ?string $actionName = null): void
    {
        $message = new DeleteActivityLogMessage($objectId, $className, $actionName);

        $this->eventDispatcher->dispatch(PreDispatchActivityLogMessageEvent::create($message));

        $this->bus->dispatch(new Envelope($message));
    }

    public function logUpdatedItem($item, ?string $actionName = null, bool $createLogWithoutChanges = false): void
    {
        if ($item instanceof LoggableChildInterface && is_object($item->logTo())) {
            $childLoggableMessage = new UpdateActivityLogMessage($item->logTo(), $actionName, $createLogWithoutChanges);
            $this->handleUpdateActivityLogMessage($childLoggableMessage);
        }

        $message = new UpdateActivityLogMessage($item, $actionName, $createLogWithoutChanges);
        $this->handleUpdateActivityLogMessage($message);
    }

    private function handleUpdateActivityLogMessage(UpdateActivityLogMessageInterface $message): void
    {
        $context = $this->loggableContextFactory->create($message->getClassName());
        if (null === $context) {
            return;
        }

        $normalizedItem = $this->normalizer->normalize($message->getUpdatedItem(), 'activityLog', $this->getNormalizationContext($context));
        $message->setNormalizedItem($normalizedItem);

        $this->eventDispatcher->dispatch(PreDispatchActivityLogMessageEvent::create($message));
        $this->bus->dispatch(new Envelope($message));
    }
}
