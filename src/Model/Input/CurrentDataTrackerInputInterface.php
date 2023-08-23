<?php

namespace Locastic\Loggastic\Model\Input;

use Locastic\Loggastic\Model\LogInterface;

interface CurrentDataTrackerInputInterface extends LogInterface
{
    public function getData(): ?string;

    public function setData(?string $data): void;

    public function getDateTime(): \DateTime;

    public function setDateTime(\DateTime $dateTime): void;
}
