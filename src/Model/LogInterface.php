<?php

namespace Locastic\ActivityLog\Model;

interface LogInterface
{
    public function getObjectId(): string;
    public function setObjectId(string $objectId): void;

    public function getObjectClass(): ?string;
    public function setObjectClass(?string $objectClass): void;
}
