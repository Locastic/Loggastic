<?php

namespace Locastic\Loggastic\Message;

use Locastic\Loggastic\Enum\ActivityLogAction;

class DeleteActivityLogMessage implements DeleteActivityLogMessageInterface
{
    private $objectId;
    private \DateTime $dateTime;
    private string $actionName;
    private string $className;
    private ?array $userInfo = null;
    private ?string $requestUrl = null;

    public function __construct($objectId, string $className, ?string $actionName = null)
    {
        $this->objectId = $objectId;
        $this->dateTime = new \DateTime();
        $this->actionName = $actionName ?? ActivityLogAction::DELETED;
        $this->className = $className;
    }

    public function getObjectId(): string
    {
        return $this->objectId;
    }

    public function getDateTime(): \DateTime
    {
        return $this->dateTime;
    }

    public function getActionName(): string
    {
        return $this->actionName;
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getUser(): ?array
    {
        return $this->userInfo;
    }

    public function setUser(?array $userInfo): void
    {
        $this->userInfo = $userInfo;
    }

    public function getRequestUrl(): ?string
    {
        return $this->requestUrl;
    }

    public function setRequestUrl(?string $requestUrl): void
    {
        $this->requestUrl = $requestUrl;
    }
}
