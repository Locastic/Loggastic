<?php

namespace Locastic\Loggastic\Model\Output;

use Symfony\Component\Serializer\Annotation\Groups;

class ActivityLog implements ActivityLogInterface
{
    protected ?string $id = null;

    #[Groups(["activity_log"])]
    protected ?string $action = null;

    #[Groups(["activity_log"])]
    protected ?\DateTime $loggedAt = null;

    #[Groups(["activity_log"])]
    protected $objectId;

    #[Groups(["activity_log"])]
    protected ?string $objectClass = null;

    #[Groups(["activity_log"])]
    protected ?array $dataChanges = null;

    #[Groups(["activity_log"])]
    protected ?string $requestUrl = null;

    #[Groups(["activity_log"])]
    protected ?array $user = null;

    protected ?string $shortName = null;

    public function __construct()
    {
        $this->loggedAt = new \DateTime();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(?string $action): void
    {
        $this->action = $action;
    }

    public function getLoggedAt(): \DateTime
    {
        return $this->loggedAt;
    }

    public function setLoggedAt(\DateTime $loggedAt): void
    {
        $this->loggedAt = $loggedAt;
    }

    public function getObjectId(): string
    {
        return (string) $this->objectId;
    }

    public function setObjectId($objectId): void
    {
        $this->objectId = (string) $objectId;
    }

    public function getObjectClass(): ?string
    {
        return $this->objectClass;
    }

    public function setObjectClass(?string $objectClass): void
    {
        $this->objectClass = $objectClass;
    }

    public function getRequestUrl(): ?string
    {
        return $this->requestUrl;
    }

    public function setRequestUrl(?string $requestUrl): void
    {
        $this->requestUrl = $requestUrl;
    }

    public function getDataChanges(): ?array
    {
        return $this->dataChanges;
    }

    public function setDataChanges(?string $dataChanges = null): void
    {
        $this->dataChanges = $dataChanges ? json_decode($dataChanges, true, 512, JSON_THROW_ON_ERROR) : null;
    }

    public function getUser(): ?array
    {
        return $this->user;
    }

    public function setUser(?array $user): void
    {
        $this->user = $user;
    }
}
