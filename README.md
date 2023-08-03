<h1 align="center">
Loggastic
</h1>

Loggastic is made for tracking changes to your objects and their relations.
Built on top of the **Symfony framework**, this library makes it easy to implement activity logs and store them on **Elasticsearch** for fast logs browsing.

Each tracked entity will have two indexes in the ElasticSearch:
1. `entity_name_activity_log` -> saving all CRUD actions made on an object. And additionally saving before and after values for Edit actions.
2. `entity_name_current_data_tracker` -> saving the latest object values used for comparing the changes made on Edit actions. This enables us to only store before and after values for modified fields in the `activity_log` index

System requirements
-------------------

Elasticsearch version 7.17

Installation
------------

`composer require locastic/loggastic`

Making your entity loggable
---------------------------

To make your entity loggable you need to do the following steps:
### 1. Add Loggable attribute to your entity

Add `Locastic\Loggastic\Annotation\Loggable` annotation to your entity and define serialization group name:
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
### 2. Add serialization groups to the fields you want to log
Use the serialization group defined in the Loggable attribute config on the fields you want to track.
You can add them to the relations and their fields too.
```php
<?php

namespace App\Entity;

use Locastic\Loggastic\Annotation\Loggable;
use Symfony\Component\Serializer\Annotation\Groups;

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
use Symfony\Component\Serializer\Annotation\Groups;

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

### 3. Run commands for creating indexes in ElasticSearch

    bin/console locastic:activity-logs:create-loggable-indexes

If you already have some data in the database, make sure to populate current data trackers with the following command:

    bin/console locastic:activity-logs:populate-current-data-trackers

### 4. Displaying activity logs
Here are the examples for displaying activity logs in twig or as Api endpoints:

**a) Displaying logs in Twig**
`Locastic\Loggastic\DataProvider\ActivityLogProviderInterface` service comes with a few useful methods for getting the activity logs data:
```php
    public function getActivityLogsByClass(string $className): array;

    public function getActivityLogsByClassAndId(string $className, $objectId): array;

    public function getActivityLogsByIndexAndId(string $index, $objectId, array $sort = []): array;
```
Use them to fetch data from the Elasticsearch and display it in your views. Example for displaying results in Twig:
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

**b) Displaying logs in the api endpoint using ApiPlatform**
In order to display Loggastic activity logs in an ApiPlatform endpoint, you can use ApiPlatforms ElasticSearch integration: https://api-platform.com/docs/core/elasticsearch/

Example for displaying activity logs in the ApiPlatform endpoint:
```yaml
# config/packages/api_platform.yaml
api_platform:
    elasticsearch:
        hosts: ['%env(ACTIVITY_LOGS_ELASTIC_URL)%']
        mapping:
            Locastic\Loggastic\Model\ActivityLog:
                index: '*_activity_log'
                type: _doc
                
    resources:
        - Locastic\Loggastic\Model\ActivityLog:
            collection_operations:
                get: ~
            item_operations:
                get: ~
            attributes:
                normalization_context:
                    groups: ['activity_log']
```

You can easily filter the results using the existing ApiPlatform filters: https://api-platform.com/docs/core/filters/


That's it!
----------
Now you have the basic activity logs setup. 
Each time some change happens in the database for loggable entities, the activity log will be saved to the Elasticsearch.

## Customization guide
Now that you have the basic setup, you can add some additional options and customize the library to your needs.

## Configuration reference
Default configuration:
```yaml
# config/packages/loggastic.yaml
locastic_loggastic:
    # directory paths containing loggable classes or xml/yaml files
    loggable_paths:
        - '%kernel.project_dir%/Resources/config/loggastic'
        - '%kernel.project_dir%/src/Entity'

    # Turn on/off the default Doctrine subscriber
    default_doctrine_subscriber: true

    # ElasticSearch config
    elastic_host: 'localhost:9200'
    elastic_date_detection: true    #https://www.elastic.co/guide/en/elasticsearch/reference/current/date-detection.html
    elastic_dynamic_date_formats: "strict_date_optional_time||epoch_millis||strict_time"

    # ElasticSearch index mapping for ActivityLog. https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-create-index.html#mappings
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
            jsonData:
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

Only one consumer should be used per loggable entity in order to not corrupt the data.

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

### Custom event listeners for saving activity logs
You can use `Locastic\Loggastic\Logger\ActivityLoggerInterface` service to save item changes to the Elasticsearch:
```php
<?php
namespace App\Service;

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
If you want to turn it off, you can do it by setting the `loggastic.doctrine_listener_enabled` config parameter to `false`:
```yaml
# config/packages/loggastic.yaml

loggastic:
    doctrine_listener_enabled: false
```

If you are using ***ApiPlatform***, one of the good options would be to use its POST_WRITE event: https://api-platform.com/docs/core/events/#custom-event-listeners

And for the ***Sylius projects*** you can use the Resource bundle events: https://docs.sylius.com/en/1.12/book/architecture/events.html

### Optimising messenger for large amount of data
Coming soon...


## Contribution

If you have idea on how to improve this bundle, feel free to contribute. If you have problems or you found some bugs, please open an issue.

## Support

Want us to help you with this bundle or any ApiPlatform/Symfony project? Write us an email on info@locastic.com
