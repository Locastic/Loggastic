<?php

namespace Locastic\ActivityLog\Model;

interface CurrentDataTrackerInterface extends LogInterface
{
    public function getId();
    public function setId($id): void;

    public function getData(): ?string;
    public function setData(string $data): void;
}
