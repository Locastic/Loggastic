<?php

namespace Locastic\Loggastic\Message;

use Locastic\Loggastic\Enum\ActivityLogAction;
use Locastic\Loggastic\Util\ClassUtils;

final class UpdateActivityLogMessage implements UpdateActivityLogMessageInterface
{
    private \DateTime $dateTime;
    private readonly string $actionName;
    private ?array $userInfo = null;
    private ?string $requestUrl = null;
    private array $normalizedItem = [];

    public function __construct(private object $updatedItem, ?string $actionName = null, private readonly bool $createLogWithoutChanges = false)
    {
        $this->dateTime = new \DateTime();
        $this->actionName = $actionName ?? ActivityLogAction::EDITED;
    }

    public function getDateTime(): \DateTime
    {
        return $this->dateTime;
    }

    public function setDateTime(\DateTime $dateTime): void
    {
        $this->dateTime = $dateTime;
    }

    public function getUpdatedItem(): object
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

    public function isCreateLogWithoutChanges(): bool
    {
        return $this->createLogWithoutChanges;
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

    public function getNormalizedItem(): array
    {
        return $this->normalizedItem;
    }

    public function setNormalizedItem(array $normalizedItem): void
    {
        $this->normalizedItem = $normalizedItem;
    }

    public function getObjectId()
    {
        return $this->getUpdatedItem()->getId();
    }

    public function getClassName(): string
    {
        return ClassUtils::getClass($this->getUpdatedItem());
    }
}
