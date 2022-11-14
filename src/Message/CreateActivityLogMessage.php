<?php

namespace Locastic\ActivityLog\Message;

use Locastic\ActivityLog\Enum\ActivityLogAction;

class CreateActivityLogMessage
{
    private $item;
    private string $actionName;
    private \DateTime $dateTime;

    public function __construct($item, ?string $actionName = null)
    {
        $this->dateTime = new \DateTime();
        $this->item = $item;
        $this->actionName = $actionName ?? ActivityLogAction::$CREATED;
    }

    public function getItem()
    {
        return $this->item;
    }

    public function getDateTime(): \DateTime
    {
        return $this->dateTime;
    }

    public function getActionName(): string
    {
        return $this->actionName;
    }
}
