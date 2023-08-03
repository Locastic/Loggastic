<?php

namespace Locastic\Loggastic\Model;

use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Save the latest data for each object, so it can be used to compare changes
 * when logging updates to activity log.
 */
class CurrentDataTracker implements CurrentDataTrackerInterface
{
    #[Groups(["current_data_tracker"])]
    protected $id;

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

    public function getId()
    {
        return $this->id;
    }

    public function setId($id): void
    {
        $this->id = $id;
    }

    public function getObjectId(): string
    {
        return (string) $this->objectId;
    }

    public function setObjectId(string $objectId): void
    {
        $this->objectId = $objectId;
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

    public function setData(string $data): void
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

    public function setDataFromArray(array $data): void
    {
        $this->data = json_encode($data, JSON_THROW_ON_ERROR);
    }

    public function getDataAsArray(): array
    {
        return json_decode($this->data, true, 512, JSON_THROW_ON_ERROR);
    }
}
