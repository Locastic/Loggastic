<?php

namespace Locastic\Loggastic\Message;

use Locastic\Loggastic\Enum\ActivityLogAction;
use Locastic\Loggastic\Util\ClassUtils;

class CreateActivityLogMessage implements CreateActivityLogMessageInterface
{
    private object $item;
    private string $actionName;
    private \DateTime $dateTime;
    private ?array $userInfo = null;
    private ?string $requestUrl = null;

    public function __construct(object $item, ?string $actionName = null)
    {
        $this->dateTime = new \DateTime();
        $this->item = $item;
        $this->actionName = $actionName ?? ActivityLogAction::CREATED;
    }

    public function getItem(): object
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

    public function getObjectId()
    {
        return $this->getItem()->getId();
    }

    public function getClassName(): string
    {
        return ClassUtils::getClass($this->getItem());
    }
}
