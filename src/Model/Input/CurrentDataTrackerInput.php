<?php

namespace Locastic\Loggastic\Model\Input;

use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Save the latest data for each object, so it can be used to compare changes
 * when logging updates to activity log.
 */
class CurrentDataTrackerInput implements CurrentDataTrackerInputInterface
{
    #[Groups(["current_data_tracker"])]
    protected $objectId;

    #[Groups(["current_data_tracker"])]
    protected \DateTime $dateTime;

    #[Groups(["current_data_tracker"])]
    protected ?string $objectClass = null;

    #[Groups(["current_data_tracker"])]
    protected ?string $data = null;

    public function __construct()
    {
        $this->dateTime = new \DateTime();
    }

    public function setObjectId(string $objectId): void
    {
        $this->objectId = $objectId;
    }

    public function getObjectId(): string
    {
        return $this->objectId;
    }

    public function getObjectClass(): ?string
    {
        return $this->objectClass;
    }

    public function setObjectClass(?string $objectClass): void
    {
        $this->objectClass = $objectClass;
    }

    public function getData(): ?string
    {
        return $this->data;
    }

    public function setData(?string $data): void
    {
        $this->data = $data;
    }

    public function getDateTime(): \DateTime
    {
        return $this->dateTime;
    }

    public function setDateTime(\DateTime $dateTime): void
    {
        $this->dateTime = $dateTime;
    }
}
