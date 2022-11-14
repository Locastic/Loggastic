<?php

namespace Locastic\ActivityLog\Message;

use Locastic\ActivityLog\Enum\ActivityLogAction;

class DeleteActivityLogMessage
{
    private string $resourceClass;
    private $itemId;
    private \DateTime $dateTime;
    private string $actionName;

    public function __construct($resourceClass, $itemId, ?string $actionName = null)
    {
        $this->resourceClass = $resourceClass;
        $this->itemId = $itemId;
        $this->dateTime = new \DateTime();
        $this->actionName = $actionName ?? ActivityLogAction::$DELETED;
    }

    public function getResourceClass(): string
    {
        return $this->resourceClass;
    }

    public function getItemId()
    {
        return $this->itemId;
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
