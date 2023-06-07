<?php

namespace Locastic\Loggastic\Event;

use Locastic\Loggastic\Model\ActivityLogInterface;
use Symfony\Contracts\EventDispatcher\Event;

class PreDispatchActivityLogEvent extends Event
{
    public function __construct(private readonly ActivityLogInterface $activityLog)
    {
    }

    public static function create(ActivityLogInterface $activityLog): self
    {
        return new self($activityLog);
    }
    
    public function getActivityLog(): ActivityLogInterface
    {
        return $this->activityLog;
    }
}
