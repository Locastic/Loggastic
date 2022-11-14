<?php

namespace Locastic\ActivityLog\Message;

use Locastic\ActivityLog\Enum\ActivityLogAction;

class UpdateActivityLogMessage
{
    private \DateTime $dateTime;
    private $updatedItem;
    private string $actionName;

    public function __construct($updatedItem, ?string $actionName = null)
    {
        $this->dateTime = new \DateTime();
        $this->updatedItem = $updatedItem;
        $this->actionName = $actionName ?? ActivityLogAction::$EDITED;
    }

    public function getDateTime(): \DateTime
    {
        return $this->dateTime;
    }

    public function setDateTime(\DateTime $dateTime): void
    {
        $this->dateTime = $dateTime;
    }

    public function getUpdatedItem()
    {
        return $this->updatedItem;
    }

    public function setUpdatedItem($updatedItem): void
    {
        $this->updatedItem = $updatedItem;
    }

    public function getActionName(): string
    {
        return $this->actionName;
    }
}
