<?php

namespace Locastic\Loggastic\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Events;
use Locastic\Loggastic\Logger\ActivityLogger;
use Locastic\Loggastic\Util\ClassUtils;

final class ActivityLogDoctrineSubscriber implements EventSubscriberInterface
{
    private array $persistedEntities = [];

    public function __construct(
        private readonly ActivityLogger $activityLogger
    ) {
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::preRemove,
            Events::postUpdate,
            Events::postRemove,
            Events::prePersist,
            Events::postFlush,
        ];
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
