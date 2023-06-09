<?php

namespace Locastic\Loggastic\Message;

interface ActivityLogMessageInterface
{
    public function getDateTime(): \DateTime;

    public function getActionName(): string;

    public function getClassName(): string;

    public function getUser(): ?array;

    public function setUser(?array $userInfo): void;

    public function getRequestUrl(): ?string;

    public function setRequestUrl(?string $requestUrl): void;

    public function getObjectId();
}
