<?php

namespace Locastic\Loggastic\Model\Input;

use Symfony\Component\Serializer\Annotation\Groups;

class ActivityLogInput implements ActivityLogInputInterface
{
    #[Groups(["activity_log"])]
    protected ?string $action = null;

    #[Groups(["activity_log"])]
    protected \DateTime $loggedAt;

    #[Groups(["activity_log"])]
    protected $objectId;

    #[Groups(["activity_log"])]
    protected ?string $objectClass = null;

    #[Groups(["activity_log"])]
    protected ?string $dataChanges = null;

    #[Groups(["activity_log"])]
    protected ?string $requestUrl = null;

    #[Groups(["activity_log"])]
    protected ?array $user = null;

    public function __construct()
    {
        $this->loggedAt = new \DateTime();
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

    public function getDataChanges(): ?string
    {
        return $this->dataChanges;
    }

    public function setDataChanges(?string $dataChanges = null): void
    {
        $this->dataChanges = $dataChanges;
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
