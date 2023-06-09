<?php

namespace Locastic\Loggastic\EventListener;

use Locastic\Loggastic\Bridge\Elasticsearch\Context\Traits\ElasticNormalizationContextTrait;
use Locastic\Loggastic\Event\PreDispatchActivityLogMessageEvent;
use Locastic\Loggastic\Loggable\LoggableChildInterface;
use Locastic\Loggastic\Message\UpdateActivityLogMessage;
use Locastic\Loggastic\Message\UpdateActivityLogMessageInterface;
use Locastic\Loggastic\Metadata\LoggableContext\Factory\LoggableContextFactoryInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class PreDispatchUpdateActivityLogMessageListener
{
    use ElasticNormalizationContextTrait;

    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly NormalizerInterface $normalizer,
        private readonly LoggableContextFactoryInterface $loggableContextFactory,
    ) {
    }

    public function __invoke(PreDispatchActivityLogMessageEvent $event)
    {
        $message = $event->getActivityLogMessage();

        if (!$message instanceof UpdateActivityLogMessageInterface) {
            return;
        }

        if ($message->getUpdatedItem() instanceof LoggableChildInterface && is_object($message->getUpdatedItem()->logTo())) {
            $this->bus->dispatch(new UpdateActivityLogMessage($message->getUpdatedItem()->logTo(), $message->getActionName(), $message->isCreateLogWithoutChanges()));
        }

        $context = $this->loggableContextFactory->create($message->getClassName());
        if (null === $context) {
            return;
        }

        $normalizedItem = $this->normalizer->normalize($message->getUpdatedItem(), 'activityLog', $this->getNormalizationContext($context));
        $message->setNormalizedItem($normalizedItem);
    }
}
