<?php

namespace Locastic\Loggastic\Model\Output;

use Locastic\Loggastic\Model\LogInterface;

interface ActivityLogInterface extends LogInterface
{
    public function getId(): ?string;

    public function setId(string $id): void;

    public function getLoggedAt(): \DateTime;

    public function setLoggedAt(\DateTime $loggedAt): void;

    public function getAction(): ?string;

    public function setAction(string $action): void;

    public function getDataChanges(): ?array;

    public function setDataChanges(?string $dataChanges = null): void;

    public function setRequestUrl(?string $requestUrl): void;

    public function getRequestUrl(): ?string;

    public function getUser(): ?array;

    public function setUser(?array $user): void;
}
