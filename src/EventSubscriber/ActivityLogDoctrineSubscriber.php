<?php

namespace Locastic\Loggastic\EventSubscriber;

use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Locastic\Loggastic\Logger\ActivityLoggerInterface;
use Locastic\Loggastic\Util\ClassUtils;

// registered as a doctrine.event_listener for each handled event, see
// config/activity_log_doctrine_subscriber.yaml (DoctrineBundle 3 removed
// event subscribers)
final class ActivityLogDoctrineSubscriber
{
    private array $persistedEntities = [];

    public function __construct(
        private readonly ActivityLoggerInterface $activityLogger,
    ) {
    }

    public function preRemove(PreRemoveEventArgs $args): void
    {
        $item = $args->getObject();
        $item->objectId = $item->getId();
    }

    public function postRemove(PostRemoveEventArgs $args): void
    {
        $item = $args->getObject();

        $this->activityLogger->logDeletedItem($item, $item->objectId, ClassUtils::getClass($item));
    }

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $item = $args->getObject();

        $this->activityLogger->logUpdatedItem($item);
    }

    public function prePersist(PrePersistEventArgs $args): void
    {
        $item = $args->getObject();

        $this->persistedEntities[] = $item;
    }

    public function postFlush(PostFlushEventArgs $args): void
    {
        if (empty($this->persistedEntities)) {
            return;
        }

        foreach ($this->persistedEntities as $item) {
            $args->getObjectManager()->refresh($item);
            $this->activityLogger->logCreatedItem($item);
        }

        $this->persistedEntities = [];
    }
}
