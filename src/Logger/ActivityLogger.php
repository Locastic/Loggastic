<?php

namespace Locastic\Loggastic\Logger;

use Locastic\Loggastic\Bridge\Elasticsearch\Context\Traits\ElasticNormalizationContextTrait;
use Locastic\Loggastic\Enum\ActivityLogAction;
use Locastic\Loggastic\Event\PreDispatchActivityLogMessageEvent;
use Locastic\Loggastic\Loggable\LoggableChildInterface;
use Locastic\Loggastic\Message\CreateActivityLogMessage;
use Locastic\Loggastic\Message\DeleteActivityLogMessage;
use Locastic\Loggastic\Message\UpdateActivityLogMessage;
use Locastic\Loggastic\Message\UpdateActivityLogMessageInterface;
use Locastic\Loggastic\MessageDispatcher\ActivityLogMessageDispatcherInterface;
use Locastic\Loggastic\Metadata\LoggableContext\Factory\LoggableContextFactoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ActivityLogger implements ActivityLoggerInterface
{
    use ElasticNormalizationContextTrait;

    public function __construct(
        private readonly ActivityLogMessageDispatcherInterface $activityLogMessageDispatcher,
        private readonly LoggableContextFactoryInterface $loggableContextFactory,
        private readonly NormalizerInterface $normalizer,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }
    public function logCreatedItem(object $item, ?string $actionName = null): void
    {
        $this->handleLoggableChild($item, $actionName);

        $message = new CreateActivityLogMessage($item, $actionName);

        $this->eventDispatcher->dispatch(PreDispatchActivityLogMessageEvent::create($message));

        $this->activityLogMessageDispatcher->dispatch($message);
    }

    public function logDeletedItem(object $item, $objectId, string $className, ?string $actionName = null): void
    {
        $this->handleLoggableChild($item, $actionName);

        $message = new DeleteActivityLogMessage($objectId, $className, $actionName);

        $this->eventDispatcher->dispatch(PreDispatchActivityLogMessageEvent::create($message));

        $this->activityLogMessageDispatcher->dispatch($message);
    }

    public function logUpdatedItem($item, ?string $actionName = null, bool $createLogWithoutChanges = false): void
    {
        $this->handleLoggableChild($item, $actionName, $createLogWithoutChanges);

        $message = new UpdateActivityLogMessage($item, $actionName, $createLogWithoutChanges);
        $this->handleUpdateActivityLogMessage($message);
    }

    private function handleLoggableChild(object $item, ?string $actionName = null, bool $createLogWithoutChanges = false): void
    {
        if ($item instanceof LoggableChildInterface && is_object($item->logTo())) {
            $childLoggableMessage = new UpdateActivityLogMessage($item->logTo(), $actionName ?: ActivityLogAction::EDITED, $createLogWithoutChanges);
            $this->handleUpdateActivityLogMessage($childLoggableMessage);
        }
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
        $this->activityLogMessageDispatcher->dispatch($message);
    }
}
