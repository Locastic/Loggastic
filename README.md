<h1 align="center">
Loggastic<br>
    <a href="https://packagist.org/packages/locastic/loggastic" title="License" target="_blank">
        <img src="https://img.shields.io/packagist/l/locastic/loggastic.svg" />
    </a>
    <a href="https://packagist.org/packages/locastic/loggastic" title="Version" target="_blank">
        <img src="https://img.shields.io/packagist/v/Locastic/loggastic.svg" />
    </a>
    <a href="https://scrutinizer-ci.com/g/Locastic/Loggastic/" title="Scrutinizer" target="_blank">
        <img src="https://img.shields.io/scrutinizer/g/Locastic/Loggastic" />
    </a>
    <a href="https://packagist.org/packages/locastic/loggastic" title="Total Downloads" target="_blank">
        <img src="https://poser.pugx.org/locastic/loggastic/downloads" />
    </a>
</h1>

Loggastic is made for tracking changes to your objects and their relations.
Built on top of the **Symfony framework**, this library makes it easy to implement activity logs and store them in **Elasticsearch** or in your **relational database** (via Doctrine DBAL).

Two kinds of records are stored for each tracked entity:
1. **Activity logs** -> saving all CRUD actions made on an object. And additionally saving before and after values for Edit actions.
2. **Current data trackers** -> saving the latest object values used for comparing the changes made on Edit actions. This enables us to only store before and after values for modified fields in the activity logs.

System requirements
-------------------

- PHP 8.2+ with Symfony 6.4, 7.x or 8.x (Symfony 8 requires PHP 8.4)
- Doctrine ORM 3.4+ with DoctrineBundle 2.8+ or 3.x
- A storage backend: Elasticsearch 8 or 9 (default), or any relational database supported by Doctrine DBAL

Installation
------------

`composer require locastic/loggastic`

Quick start
-----------

Mark an entity as loggable with the `Loggable` attribute and put the serialization group on every field you want to track:

```php
<?php

namespace App\Entity;

use Locastic\Loggastic\Annotation\Loggable;
use Symfony\Component\Serializer\Attribute\Groups;

#[Loggable(groups: ['blog_post_log'])]
class BlogPost
{
    private int $id;

    #[Groups(groups: ['blog_post_log'])]
    private string $title;

    // ...
}
```

Initialize the storage (Elasticsearch indexes or database tables):

```bash
bin/console locastic:activity-logs:create-loggable-indexes
```

Every create, update and delete on `BlogPost` is now logged automatically. Read the logs back with the `Locastic\Loggastic\DataProvider\ActivityLogProviderInterface` service:

```php
$activityLogs = $activityLogProvider->getActivityLogsByClassAndId(BlogPost::class, $blogPost->getId());
```

Logs are stored in Elasticsearch by default. Set `locastic_loggastic.storage: doctrine` to store them in your relational database instead (see the next section). The rest of this README covers each step in detail.

Choose your storage
-------------------

Activity logs are stored in **Elasticsearch** by default, which is a great fit for fast log browsing on high-traffic data. Requirements: Elasticsearch 8 or 9, and a PSR-18 HTTP client implementation for the Elasticsearch client (for example `composer require symfony/http-client nyholm/psr7`). Each loggable entity gets two indexes: `entity_name_activity_log` and `entity_name_current_data_tracker`.

If you don't want to run an Elasticsearch cluster, store the logs in your existing **relational database** instead (PostgreSQL, MySQL, SQLite, or anything else supported by Doctrine DBAL):

```yaml
# config/packages/loggastic.yaml
locastic_loggastic:
    storage: doctrine
```

The Doctrine storage uses your default DBAL connection and keeps all activity logs in two shared tables (`loggastic_activity_log` and `loggastic_current_data_tracker`), with JSON columns for changes data. Timestamps are stored in UTC. No Elasticsearch dependency or configuration is needed.

For test suites there is also an `in_memory` storage that keeps logs in the PHP process, so the full logging flow runs without any external service.

Making your entity loggable
---------------------------

To make your entity loggable you need to do the following steps:
### 1. Add Loggable attribute to your entity

Add the `Locastic\Loggastic\Annotation\Loggable` PHP attribute to your entity and define serialization group name:
```php
<?php

namespace App\Entity;

use Locastic\Loggastic\Annotation\Loggable;

#[Loggable(groups: ['blog_post_log'])]
class BlogPost
{    
    // ...
}
```

If you are using YAML:
```yaml
locastic_loggable:
        - { class: 'App\Entity\BlogPost', groups: [ 'blog_post_log' ] }
```

Or XML:
```xml
<?xml version="1.0" ?>

<locastic_loggable_classes xmlns="https://locastic.com/schema/metadata/loggable"
                           xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                           xsi:schemaLocation="https://locastic.com/schema/metadata/loggable
           https://locastic.com/schema/metadata/loggable.xsd" >
    <loggable_class class="App\Entity\BlogPost">
        <group name="blog_post_log"/>
    </loggable_class>
</locastic_loggable_classes>
```

### 2. Add serialization groups to the fields you want to log
Use the serialization group defined in the Loggable attribute config on the fields you want to track.
You can add them to the relations and their fields too.
```php
<?php

namespace App\Entity;

use Locastic\Loggastic\Annotation\Loggable;
use Symfony\Component\Serializer\Attribute\Groups;

#[Loggable(groups: ['blog_post_log'])]
class BlogPost
{
    private int $id;

    #[Groups(groups: ['blog_post_log'])]
    private string $title;

    #[Groups(groups: ['blog_post_log'])]
    private ArrayCollection $tags;
    
    // ...
}
```

Example for logging fields from relations:

```php
<?php

namespace App\Entity;

use Locastic\Loggastic\Annotation\Loggable;
use Symfony\Component\Serializer\Attribute\Groups;

class Tag
{
    private int $id;

    #[Groups(groups: ['blog_post_log'])]
    private string $name;

    #[Groups(groups: ['blog_post_log'])]
    private DateTimeImmutable $createdAt;
    
    // ...
}
```

Note: You can also use **annotations, xml and yaml**! Examples coming soon.

### 3. Initialize the storage

Create the Elasticsearch indexes or database tables for your loggable classes:

```bash
bin/console locastic:activity-logs:create-loggable-indexes
```

If you already have some data in the database, make sure to populate current data trackers with the following command:

```bash
bin/console locastic:activity-logs:populate-current-data-trackers
```

### 4. Displaying activity logs
Here are the examples for displaying activity logs in twig or as Api endpoints:

#### Display activity logs in Twig
`Locastic\Loggastic\DataProvider\ActivityLogProviderInterface` service comes with a few useful methods for getting the activity logs data:
```php
    public function getActivityLogsByClass(string $className, array $sort = []): array;

    public function getActivityLogsByClassAndId(string $className, $objectId, array $sort = []): array;
```
If you need to read logs directly from a specific Elasticsearch index, use
`Locastic\Loggastic\Bridge\Elasticsearch\Storage\ElasticsearchActivityLogStorage::findByIndexAndObjectId()`.
Use them to fetch the activity logs from the configured storage and display them in your views. Example for displaying results in Twig:
```twig
Activity logs for Blog Posts:
<br>
{% for log in activityLogs %}
    {{ log.action }} {{ log.objectType }} with {{ log.objectId }} ID at {{ log.loggedAt|date('d.m.Y H:i:s') }} by {{ log.user.username }}
{% endfor %}
```
The output would look something like this:
```text
Activity logs for Blog Posts:

Created BlogPost with 1 ID at 01.01.2023 12:00:00 by admin
Updated BlogPost with 1 ID at 02.01.2023 08:30:00 by admin
Deleted BlogPost with 1 ID at 01.01.2023 12:00:00 by admin
```

#### Expose activity logs as an API Platform endpoint (Elasticsearch storage)
In order to display Loggastic activity logs in an ApiPlatform endpoint, you can use ApiPlatforms ElasticSearch integration (this approach only applies to the `elasticsearch` storage): https://api-platform.com/docs/core/elasticsearch/

Example for displaying activity logs in the ApiPlatform endpoint:
```php
#[ApiResource(
    operations: [
        new Get(provider: ItemProvider::class),
        new GetCollection(provider: CollectionProvider::class),
    ],
    order: ["loggedAt" => "DESC"],
    stateOptions: new Options(index: '*_activity_log'),
)]
class ActivityLog extends BaseActivityLog
{
    #[ApiProperty(identifier: true)]
    protected ?string $id = null;
}
```

You can easily filter the results using the existing ApiPlatform filters: https://api-platform.com/docs/core/filters/. 
If you want to have different fields in the response, use serialization groups or even create a custom DTO.

Using `*_activity_log` index will return all activity logs. 
If you want to return only logs for one entity, use the exact index name. For example if you only want to show `BlogPost` entity logs, use `blog_post_activity_log` index in `stateOptions` config.


That's it!
----------
Now you have the basic activity logs setup. 
Each time some change happens in the database for loggable entities, the activity log will be saved to the configured storage.

## Customization guide
Now that you have the basic setup, you can add some additional options and customize the library to your needs.

### Custom storage backends
The built-in backends are selected with the `storage` config option (`elasticsearch`, `doctrine` or `in_memory`). The core services only talk to three storage interfaces:

```php
Locastic\Loggastic\Storage\ActivityLogStorageInterface        # writes and reads activity logs
Locastic\Loggastic\Storage\CurrentDataTrackerStorageInterface # tracks the latest state of each loggable object
Locastic\Loggastic\Storage\StorageInitializerInterface        # creates the underlying storage (indexes, tables, ...)
```

To store logs somewhere else, implement the three interfaces and alias them to your services:

```yaml
# config/services.yaml
services:
    Locastic\Loggastic\Storage\ActivityLogStorageInterface: '@App\Loggastic\MyActivityLogStorage'
    Locastic\Loggastic\Storage\CurrentDataTrackerStorageInterface: '@App\Loggastic\MyCurrentDataTrackerStorage'
    Locastic\Loggastic\Storage\StorageInitializerInterface: '@App\Loggastic\MyStorageInitializer'
```

The loggers, message handlers, data providers and console commands will use your implementations without any further changes.

## Configuration reference
All options live under the `locastic_loggastic` key in `config/packages/loggastic.yaml`. The values below are the defaults.

### General options

```yaml
# config/packages/loggastic.yaml
locastic_loggastic:
    # storage backend for activity logs: 'elasticsearch', 'doctrine' or 'in_memory'
    storage: elasticsearch

    # directory paths containing loggable classes or xml/yaml files
    loggable_paths:
        - '%kernel.project_dir%/Resources/config/loggastic'
        - '%kernel.project_dir%/src/Entity'

    # Turn on/off the default Doctrine subscriber
    default_doctrine_subscriber: true

    # Turn on/off collection identifier extractor 
    # if set to `true` objects identifiers in collections will be used as array keys
    # if set to `false` default numeric array keys will be used
    identifier_extractor: true
```

### Elasticsearch connection options

Only used when `storage` is `elasticsearch`:

```yaml
# config/packages/loggastic.yaml
locastic_loggastic:
    elastic_host: 'localhost:9200'
    elastic_user: null              # basic auth username, for secured clusters
    elastic_password: null          # basic auth password, for secured clusters
    elastic_ssl_verification: true  # disable only for local development
    elastic_date_detection: true    #https://www.elastic.co/guide/en/elasticsearch/reference/current/date-detection.html
    elastic_dynamic_date_formats: "strict_date_optional_time||epoch_millis||strict_time"
```

### Elasticsearch index mappings

Index mappings for the activity log and current data tracker indexes, only used when `storage` is `elasticsearch` (see https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-create-index.html#mappings):

```yaml
# config/packages/loggastic.yaml
locastic_loggastic:
    # ElasticSearch index mapping for ActivityLog
    activity_log:
        elastic_properties:
            id:
                type: keyword
            action:
                type: text
            loggedAt:
                type: date
            objectId:
                type: text
            objectType:
                type: text
            objectClass:
                type: text
            dataChanges:
                type: text
            user:
                type: object
                properties:
                    username:
                        type: text

    # ElasticSearch index mapping for CurrentDataTracker
    current_data_tracker:
        elastic_properties:
            dateTime:
                type: date
            objectId:
                type: text
            objectType:
                type: text
            objectClass:
                type: text
            data:
                type: text
```

### Saving logs async
Activity logs are using Symfony messenger component and are made to work in the async way too.
If you want to make them async add the following messages to the messenger config:
```yaml
framework:
    messenger:
        routing:
            'Locastic\Loggastic\Message\PopulateCurrentDataTrackersMessage': async
            'Locastic\Loggastic\Message\CreateActivityLogMessage': async
            'Locastic\Loggastic\Message\DeleteActivityLogMessage': async
            'Locastic\Loggastic\Message\UpdateActivityLogMessage': async
```

***Important note!***

Only one consumer should be used per loggable object in order to not corrupt the data.

### Optimising messenger for large amount of data
If you have a large amount of data, you might need more than one consumer to process the messages.
In that case, you can configure different transports for the messages and use different consumer for each one.
First step is to configure the transports. Here are the examples for AMQP and Doctrine transports for the `activity_logs_default` and `activity_logs_product` queues:

AMQP transport config example:
```yaml
framework:
    messenger:
        transports:
             activity_logs_default:
                dsn: '%env(MESSENGER_TRANSPORT_DSN)%'
                options:
                    exchange:
                        name: activity_logs_default
                    queues:
                        activity_logs_default: ~
             activity_logs_product:
                dsn: '%env(MESSENGER_TRANSPORT_DSN)%'
                options:
                    queues:
                        activity_logs_product: ~
                    exchange:
                        name: activity_logs_product

        routing:
            'Locastic\Loggastic\Message\PopulateCurrentDataTrackersMessage': activity_logs_default
            'Locastic\Loggastic\Message\CreateActivityLogMessage': activity_logs_default
            'Locastic\Loggastic\Message\DeleteActivityLogMessage': activity_logs_default
            'Locastic\Loggastic\Message\UpdateActivityLogMessage': activity_logs_default
```

Doctrine transport config example:
```yaml
framework:
    messenger:
        transports:
             activity_logs_default:
                dsn: '%env(MESSENGER_TRANSPORT_DSN)%'
                options:
                    queue_name: activity_logs_default
             activity_logs_product:
                dsn: '%env(MESSENGER_TRANSPORT_DSN)%'
                options:
                    queue_name: activity_logs_product
        routing:
            'Locastic\Loggastic\Message\PopulateCurrentDataTrackersMessage': activity_logs_default
            'Locastic\Loggastic\Message\CreateActivityLogMessage': activity_logs_default
            'Locastic\Loggastic\Message\DeleteActivityLogMessage': activity_logs_default
            'Locastic\Loggastic\Message\UpdateActivityLogMessage': activity_logs_default
```

Next step is to decorate `ActivityLogDispatcher` and add your own logic for dispatching messages to the transports.
In this example we are sending all messages to the `activity_logs_default` transport except the ones for the `Product` entity which are sent to the `activity_logs_product` transport:

```php
<?php

namespace App\MessageDispatcher;

use App\Entity\Product;
use Locastic\Loggastic\Message\ActivityLogMessageInterface;
use Locastic\Loggastic\MessageDispatcher\ActivityLogMessageDispatcherInterface;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;

#[AsDecorator(ActivityLogMessageDispatcherInterface::class)]
class ActivityLogMessageDispatcher implements ActivityLogMessageDispatcherInterface
{
    public function __construct(private readonly ActivityLogMessageDispatcherInterface $decorated)
    {
    }

    public function dispatch(ActivityLogMessageInterface $activityLogMessage, ?string $transportName = null): void
    {
        if ($activityLogMessage->getClassName() === Product::class) {
            $this->decorated->dispatch($activityLogMessage, 'activity_logs_product');

            return;
        }

        $this->decorated->dispatch($activityLogMessage, $transportName);
    }
}
```

Depending on your project needs, you can have more transports and dispatch messages to them based on your own logic.

### Handling relations
Sometimes you want to log changes made on some entity to some related entity. For example if you are using the Doctrine listener, you will only get the entity that actually had changes.
Let's say you want to log `Product` changes which has a relation to the `ProductVariant`. On the edit form only fields from the `ProductVariant` were changed.
Even if you run `persist()` method on `Product`, in this case only `ProductVariant` will be shown in the Doctrine listener.
For this case you can use the `Locastic\Loggastic\Loggable\LoggableChildInterface` on `ProductVariant`:
```php
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

**Warning:** `logTo()` must still return the parent at the moment the child is deleted, or the removal will not be logged. Entity `remove*()` methods generated by the maker bundle set the owning side to null:

```php
public function removeProductVariant(ProductVariant $productVariant): static
{
    if ($this->productVariants->removeElement($productVariant)) {
        if ($productVariant->getProduct() === $this) {
            $productVariant->setProduct(null); // breaks logTo(), remove this
        }
    }

    return $this;
}
```

With `orphanRemoval` enabled the variant is deleted as soon as it leaves the collection, so nulling the owning side is unnecessary, and it makes `logTo()` return null while Loggastic is logging the removal, silently dropping the parent's activity log. Keep the owning side set instead.

### Custom event listeners for saving activity logs
You can use `Locastic\Loggastic\Logger\ActivityLoggerInterface` service to save item changes to the configured storage:
```php
<?php
namespace App\Service;

use Locastic\Loggastic\Logger\ActivityLoggerInterface;

class SomeService
{
    public function __construct(private readonly ActivityLoggerInterface $activityLogger)
    {
    }
    
    public function logItem($item): void
    {
        $this->activityLogger->logCreatedItem($item, 'custom_action_name');
        $this->activityLogger->logDeletedItem($item->getId(), get_class($item), 'custom_action_name');
        $this->activityLogger->logUpdatedItem($item, 'custom_action_name');
    }
}
```
Depending on you application logic, you need to find the most fitting place to trigger logs saving.

In most cases that can be the ***Doctrine event listener*** which is triggered on each database change. Loggastic comes with a built-in Doctrine listener which is used by default.
If you want to turn it off, you can do it by setting the `default_doctrine_subscriber` config option to `false`:
```yaml
# config/packages/loggastic.yaml

locastic_loggastic:
    default_doctrine_subscriber: false
```

If you are using ***ApiPlatform***, one of the good options would be to use its POST_WRITE event: https://api-platform.com/docs/core/events/#custom-event-listeners

And for the ***Sylius projects*** you can use the Resource bundle events: https://docs.sylius.com/en/1.12/book/architecture/events.html

### Save activity logs when no data changes were made
Sometimes you want to save activity logs even if no data changes were made. 
For example if you want to log order confirmation email was sent or some PDF was downloaded.

You can do that by setting the 3rd parameter to true:
```php
$this->activityLogger->logUpdatedItem($item, 'Order confirmation sent', true);
```

## Contribution

If you have idea on how to improve this bundle, feel free to contribute. If you have problems or you found some bugs, please open an issue.

## Support

Want us to help you with this bundle or any ApiPlatform/Symfony project? Write us an email on info@locastic.com
