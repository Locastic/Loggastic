<?php

namespace Locastic\ActivityLog\Model;

use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Save the latest data for each object, so it can be used to compare changes
 * when logging updates to activity log.
 */
class CurrentDataTracker implements CurrentDataTrackerInterface
{
    protected $id;

    /**
     * @Groups({"current_data_tracker_elastic", "current_data_tracker"})
     */
    protected $objectId;

    /**
     * @Groups({"current_data_tracker_elastic", "current_data_tracker"})
     */
    protected \DateTime $dateTime;

    /**
     * @Groups({"current_data_tracker_elastic", "current_data_tracker"})
     */
    protected ?string $objectClass = null;

    /**
     * @Groups({"current_data_tracker_elastic", "current_data_tracker"})
     */
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
        return (string)$this->objectId;
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

    public function setDataFromArray(array $data): void
    {
        $this->data = json_encode($data);
    }

    public function getDataAsArray(): array
    {
        return json_decode($this->data, true);
    }
}
