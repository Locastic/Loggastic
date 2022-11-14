<?php

namespace Locastic\ActivityLog\Model;

use Symfony\Component\Security\Core\User\UserInterface;

interface ActivityLogInterface extends LogInterface
{
    public function getId(): string;

    public function getLoggedAt():\DateTime;
    public function setLoggedAt(\DateTime $loggedAt): void;

    public function getAction(): ?string;
    public function setAction(string $action): void;

    public function getData(): array;
    public function setData(array $data): void;

    public function getUser(): ?UserInterface;
    public function setUser(?UserInterface $user): void;
}
