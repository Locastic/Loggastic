<?php

namespace Locastic\Loggastic\Event;

use Locastic\Loggastic\Message\ActivityLogMessageInterface;
use Locastic\Loggastic\Model\ActivityLogInterface;
use Symfony\Contracts\EventDispatcher\Event;

class PreDispatchActivityLogMessageEvent extends Event
{
    public function __construct(private readonly ActivityLogMessageInterface $activityLogMessage)
    {
    }

    public static function create(ActivityLogMessageInterface $activityLogMessage): self
    {
        return new self($activityLogMessage);
    }

    public function getActivityLogMessage(): ActivityLogMessageInterface
    {
        return $this->activityLogMessage;
    }
}
