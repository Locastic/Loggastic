[WIP]
# Locastic Activity Logs

Locastic Activity Logs is made for tracking changes to your objects and their relations.
Built on top of the **Symfony framework**, this library makes it easy to implement activity logs and store them on **Elasticsearch** for fast logs browsing.

Each tracked entity will have two indexes in the ElasticSearch:
1. `entity_name_activity_log` -> saving all CRUD actions made on an object. And additionally saving before and after values for Edit actions.
2. `entity_name_current_data_tracker` -> saving the latest object values used for comparing the changes made on Edit actions. This enables us to only store before and after values for modified fields in the `activity_log` index

## System requirements
Elasticsearch version 7.17

## Installation

`composer require locastic/activity-logs`

(Not yet publicly available)

## Making your entity loggable
### 1. Add Loggable annotation to your entity

Add `Locastic\Loggastic\Annotation\Loggable` annotation to your entity and define serialization group name:
```
<?php

namespace Locastic\Loggastic\Tests\Fixtures;

use Locastic\Loggastic\Annotation\Loggable;

/**
 * @Loggable(groups={"blog_post_log"})
 */
class BlogPost
{
    private int $id;

    private ?string $title;

    private array $tags = [];
    
    // ...
}
```
### 2. Add serialization group to the fields you want to log
Use the serialization group defined in the Loggable annotation on the fields you want to track.
You can add them to the relations and their fields too.
```
<?php

namespace App\Entity;

use Locastic\Loggastic\Annotation\Loggable;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @Loggable(groups={"blog_post_log"})
 */
class BlogPost
{
    private int $id;

    /** @Groups({"blog_post_log"}) */
    private ?string $title;

    /** @Groups({"blog_post_log"}) */
    private array $tags = [];
    
    // ...
}
```

### 3. Run commands for creating indexes in ElasticSearch

    bin/console locastic:activity-logs:create-loggable-indexes

If you already have some data in the database, make sure to populate current data trackers with the following command:

    bin/console locastic:activity-logs:populate-current-data-trackers

### 4. Add an event listener for dispatching logs messages on CRUD actions
Depending on you application logic, you need to find the most fitting place to trigger logs saving. In most cases that can be ***Doctrine event listener*** which is triggered on each database change.

If you are using ***ApiPlatform***, one of the good options would be to use its POST_WRITE event: https://api-platform.com/docs/core/events/#custom-event-listeners

And for the ***Sylius projects*** you can use the Resource bundle events: https://sylius-try.readthedocs.io/en/latest/bundles/general/events.html

Here is an example for the Doctrine listener implementation:
```
<?php

namespace App\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Locastic\Loggastic\Message\DeleteActivityLogMessage;
use Locastic\Loggastic\Util\ClassUtils;
use Symfony\Component\Messenger\MessageBusInterface;

class ActivityLogDoctrineSubscriber implements EventSubscriber
{
    private MessageBusInterface $bus;

    private array $persistedEntities = [];
    
    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    public function getSubscribedEvents()
    {
        return [
            'prePersist',
            'postFlush',
            'postRemove',
            'preRemove',
            'postUpdate',
            'postSoftDelete',
        ];
    }
    
    public function prePersist(LifecycleEventArgs $args): void
    {
        $this->persistedEntities[] = $args->getObject();
    }
    
    public function postFlush(PostFlushEventArgs $args): void
    {
        if (empty($this->persistedEntities)) {
            return;
        }
        
        foreach ($this->persistedEntities as $key => $item) {
            $args->getEntityManager()->refresh($item);
            $this->bus->dispatch(new CreateActivityLogMessage($item));
        }   
        
        $this->persistedEntities = [];
    }
    
    public function postUpdate(LifecycleEventArgs $args): void
    {
        $item = $args->getObject();
        
        $this->bus->dispatch(new UpdateActivityLogMessage($item));
    }

    public function preRemove(LifecycleEventArgs $args): void
    {
        $item = $args->getObject();
        $item->objectId = $item->getId();
    }

    public function postRemove(LifecycleEventArgs $args): void
    {
        $item = $args->getObject();

        $this->bus->dispatch(new DeleteActivityLogMessage($item->objectId, ClassUtils::getClass($item)));
    }

    public function postSoftDelete(LifecycleEventArgs $args): void
    {
        $item = $args->getObject();

        $this->bus->dispatch(new DeleteActivityLogMessage($item->getId(), ClassUtils::getClass($item)));
    }
}
```

If you have any custom actions which are not covered by the listener you made, make sure to trigger logs saving manually.

### 5. Displaying activity logs
`Locastic\Loggastic\DataProvider\ActivityLogProvider` comes with a few useful methods for getting the activity logs data:

    public function getActivityLogsByClass(string $className): array;

    public function getActivityLogsByClassAndId(string $className, $objectId): array;

    public function getActivityLogsByIndexAndId(string $index, $objectId, array $sort = []): array;

You can use them or add your own. It's up to you to create the actual views. Or if you are using ApiPlatform to create GET endpoints and return data using the provider.


## Saving logs async
Activity logs are using Symfony messenger component and are made to work in the async way.
If you want to make them async add the following messages to the messenger config:

        routing:
            'Locastic\Loggastic\Message\PopulateCurrentDataTrackersMessage': async
            'Locastic\Loggastic\Message\CreateActivityLogMessage': async
            'Locastic\Loggastic\Message\DeleteActivityLogMessage': async
            'Locastic\Loggastic\Message\UpdateActivityLogMessage': async

***Important note!***

Only one consumer should be used per entity in order to not corrupt the data.

## Handling relations
Sometimes you want to log changes on some entity to some related entity. For example if you are using the Doctrine listener, you will only get the entity that actually had changes.
Let's say you want to log `Product` changes which has a relation to the `ProductVariant`. On the edit form only fields from the `ProductVariant` were changed.
Even if you run `persist()` method on `Product`, in this case only ProductVariant will be shown in the Doctrine listener.
For this case you can use the `Locastic\Loggastic\Loggable\LoggableChildInterface` on `ProductVariant`:
```
<?php

namespace App\Entity;

use Locastic\Loggastic\Loggable\LoggableChildInterface;

class ProductVariant implements LoggableChildInterface
{
    private Product $product;
    
    public function getProduct(): Product
    {
        return $this->product;
    }
    
    public function logTo(): ?object
    {
        return $this->getProduct();
    }
    
    // ...
}
```

Now each change made on `ProductVariant` will be logged to the `Product`.

## Customizing guide
TODO

## Configuration
TODO : annotations, attributes, xml, yaml

## Optimising messenger for large amount of data
TODO
