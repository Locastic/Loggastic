<?php

namespace Locastic\Loggastic\MessageDispatcher;

use Locastic\Loggastic\Message\ActivityLogMessageInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\TransportNamesStamp;

class ActivityLogMessageDispatcher implements ActivityLogMessageDispatcherInterface
{
    public function __construct(private readonly MessageBusInterface $bus)
    {
    }

    public function dispatch(ActivityLogMessageInterface $activityLogMessage, ?string $transportName = null): void
    {
        $stamps = [];
        if($transportName !== null) {
            $stamps[] = new TransportNamesStamp($transportName);
        }
        $this->bus->dispatch(new Envelope($activityLogMessage), $stamps);
    }
}
