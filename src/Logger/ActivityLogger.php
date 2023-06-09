<?php

namespace Locastic\Loggastic\Logger;

use Locastic\Loggastic\Bridge\Elasticsearch\Context\Traits\ElasticNormalizationContextTrait;
use Locastic\Loggastic\Event\PreDispatchActivityLogMessageEvent;
use Locastic\Loggastic\Loggable\LoggableChildInterface;
use Locastic\Loggastic\Message\CreateActivityLogMessage;
use Locastic\Loggastic\Message\DeleteActivityLogMessage;
use Locastic\Loggastic\Message\UpdateActivityLogMessage;
use Locastic\Loggastic\Metadata\LoggableContext\Factory\LoggableContextFactoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ActivityLogger implements ActivityLoggerInterface
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

        $this->bus->dispatch($message);
    }

    public function logDeletedItem($objectId, string $className, ?string $actionName = null): void
    {
        $message = new DeleteActivityLogMessage($objectId, $className, $actionName);

        $this->eventDispatcher->dispatch(PreDispatchActivityLogMessageEvent::create($message));

        $this->bus->dispatch($message);
    }

    public function logUpdatedItem($item, ?string $actionName = null, bool $createLogWithoutChanges = false)
    {
        $message = new UpdateActivityLogMessage($item, $actionName, $createLogWithoutChanges);

        if ($message->getUpdatedItem() instanceof LoggableChildInterface && is_object($message->getUpdatedItem()->logTo())) {
            $this->bus->dispatch(new UpdateActivityLogMessage($message->getUpdatedItem()->logTo(), $message->getActionName(), $message->isCreateLogWithoutChanges()));
        }

        $context = $this->loggableContextFactory->create($message->getClassName());
        if (null === $context) {
            return;
        }

        $normalizedItem = $this->normalizer->normalize($message->getUpdatedItem(), 'activityLog', $this->getNormalizationContext($context));

        $message->setNormalizedItem($normalizedItem);

        $this->eventDispatcher->dispatch(PreDispatchActivityLogMessageEvent::create($message));

        $this->bus->dispatch($message);
    }
}
