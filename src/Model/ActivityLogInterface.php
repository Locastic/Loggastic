<?php

namespace Locastic\Loggastic\Model;

interface ActivityLogInterface extends LogInterface
{
    public function getId(): ?string;

    public function setId(string $id): void;

    public function getLoggedAt(): \DateTime;

    public function setLoggedAt(\DateTime $loggedAt): void;

    public function getAction(): ?string;

    public function setAction(string $action): void;

    /** @deprecated  */
    public function getData(): ?array;

    /** @deprecated  */
    public function setData(?array $data): void;

    public function getDataChanges(): string;

    public function getDataChangesArray(): ?array;

    public function setDataChangesFromArray(?array $dataChanges = null): void;

    public function setDataChanges(string $dataChanges): void;

    public function setRequestUrl(?string $requestUrl): void;

    public function getRequestUrl(): ?string;

    public function getUser(): ?array;

    public function setUser(?array $user): void;

    public function getShortName(): ?string;
}
