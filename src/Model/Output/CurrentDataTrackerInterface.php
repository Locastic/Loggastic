<?php

namespace Locastic\Loggastic\Model\Output;

use Locastic\Loggastic\Model\LogInterface;

interface CurrentDataTrackerInterface extends LogInterface
{
    public function getId();

    public function setId($id): void;

    public function getData(): ?array;

    public function setData(string $data): void;
}
