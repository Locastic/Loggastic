<?php

namespace Locastic\Loggastic\Message;

use Locastic\Loggastic\Enum\ActivityLogAction;

final class DeleteActivityLogMessage implements DeleteActivityLogMessageInterface
{
    private readonly \DateTime $dateTime;
    private readonly string $actionName;
    private ?array $userInfo = null;
    private ?string $requestUrl = null;

    public function __construct(private $objectId, private readonly string $className, ?string $actionName = null)
    {
        $this->dateTime = new \DateTime();
        $this->actionName = $actionName ?? ActivityLogAction::DELETED;
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
