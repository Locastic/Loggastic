<?php

namespace Locastic\ActivityLog\Model;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

class ActivityLog implements ActivityLogInterface
{
    /**
     * @Groups({"activity_log_elastic", "activity_log"})
     */
    protected ?string $action = null;

    /**
     * @Groups({"activity_log_elastic", "activity_log"})
     */
    protected ?\DateTime $loggedAt = null;

    /**
     * @Groups({"activity_log_elastic", "activity_log"})
     */
    protected $objectId;

    /**
     * @Groups({"activity_log_elastic", "activity_log"})
     */
    protected ?string $objectClass = null;

    /**
     * @Groups({"activity_log_elastic", "activity_log"})
     */
    protected array $data = [];

    /**
     * @Groups({"activity_log_elastic", "activity_log"})
     */
    protected ?string $requestUrl = null;

    /**
     * @Groups({"activity_log_elastic", "activity_log"})
     */
    protected ?UserInterface $user = null;

    public function __construct()
    {
        $this->loggedAt = new \DateTime();
    }

    public function getId(): string
    {
        return sha1($this->getLoggedAt()->getTimestamp().'-'.$this->getObjectId().'-'.random_int(1000,9999));
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
        return (string)$this->objectId;
    }

    public function setObjectId($objectId): void
    {
        $this->objectId = (string)$objectId;
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

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): void
    {
        $this->data = $data;
    }

    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    public function setUser(?UserInterface $user): void
    {
        $this->user = $user;
    }
}
