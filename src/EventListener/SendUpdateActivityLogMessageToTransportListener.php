<?php

namespace Locastic\Loggastic\EventListener;

use Locastic\Loggastic\Bridge\Elasticsearch\Context\Traits\ElasticNormalizationContextTrait;
use Locastic\Loggastic\Loggable\LoggableChildInterface;
use Locastic\Loggastic\Message\UpdateActivityLogMessage;
use Locastic\Loggastic\Message\UpdateActivityLogMessageInterface;
use Locastic\Loggastic\Metadata\LoggableContext\Factory\LoggableContextFactoryInterface;
use Symfony\Component\Messenger\Event\SendMessageToTransportsEvent;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class SendUpdateActivityLogMessageToTransportListener
{
    use ElasticNormalizationContextTrait;

    private NormalizerInterface $normalizer;
    private LoggableContextFactoryInterface $loggableContextFactory;
    private MessageBusInterface $bus;

    public function __construct(MessageBusInterface $bus, NormalizerInterface $normalizer, LoggableContextFactoryInterface $loggableContextFactory)
    {
        $this->normalizer = $normalizer;
        $this->loggableContextFactory = $loggableContextFactory;
        $this->bus = $bus;
    }

    public function __invoke(SendMessageToTransportsEvent $event): void
    {
        $message = $event->getEnvelope()->getMessage();

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
