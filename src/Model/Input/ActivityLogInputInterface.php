<?php

namespace Locastic\Loggastic\Model\Input;

use Locastic\Loggastic\Model\LogInterface;

interface ActivityLogInputInterface extends LogInterface
{
    public function getLoggedAt(): \DateTime;

    public function setLoggedAt(\DateTime $loggedAt): void;

    public function getAction(): ?string;

    public function setAction(string $action): void;

    public function getDataChanges(): ?string;

    public function setDataChanges(?string $dataChanges): void;

    public function setRequestUrl(?string $requestUrl): void;

    public function getRequestUrl(): ?string;

    public function getUser(): ?array;

    public function setUser(?array $user): void;
}
