# Upgrading to 2.0

This document collects the breaking changes planned for 2.0. Each entry lists
the deprecation shipped in a 1.x release so you can migrate before upgrading.

## Symfony 8 and serializer attributes

- Symfony 8 is now supported (`^6.4 || ^7.0 || ^8.0`), together with
  DoctrineBundle 3. On Symfony 8 stacks Doctrine uses PHP 8.4 native lazy
  objects; the previous `symfony/var-exporter < 8.0` restriction is gone.
- Import `Groups` from `Symfony\Component\Serializer\Attribute` instead of
  `Symfony\Component\Serializer\Annotation` on your loggable entities. The
  `Annotation` namespace was removed in Symfony 8; the `Attribute` namespace
  exists since Symfony 6.4, so this change is safe on every supported
  version. On Symfony 8, entities still using the old import are silently
  not logged (the group metadata is invisible), so update the imports before
  upgrading.
- `ActivityLogDoctrineSubscriber` no longer implements DoctrineBundle's
  `EventSubscriberInterface` (removed in DoctrineBundle 3). It is registered
  via per-event `doctrine.event_listener` tags. If you decorated or replaced
  this service, mirror that registration.

## Bundle structure

- The bundle uses the modern directory layout: service configuration lives in
  `config/` at the bundle root instead of `src/Resources/config/`, and the
  bundle class extends `AbstractBundle`. The
  `Locastic\Loggastic\DependencyInjection\LocasticLoggasticExtension` and
  `Locastic\Loggastic\DependencyInjection\Configuration` classes were removed.
  Application config (the `locastic_loggastic` key, all options, service IDs
  and aliases) is unchanged; only code referencing those two internal classes
  or bundle-internal file paths needs updating.

## Elasticsearch client and server

- The bundle now requires `elasticsearch/elasticsearch` `^8.0 || ^9.0` and an
  Elasticsearch 8 or 9 server. Elasticsearch 7 is end of life and no longer
  supported.
- The Elasticsearch client no longer ships an HTTP client: install any PSR-18
  implementation, for example `composer require symfony/http-client nyholm/psr7`.
- `ElasticsearchClientInterface::getClient()` returns
  `Elastic\Elasticsearch\Client` (new vendor namespace) instead of
  `Elasticsearch\Client`. Custom decorators or direct client usage must update
  their imports. Search responses are now response objects; they still support
  array access, so `$response['hits']['hits']` keeps working.
- New config options for secured clusters: `elastic_user`, `elastic_password`
  and `elastic_ssl_verification`.

## Metadata

- `AnnotationLoggableContextCollectionFactory` (deprecated since 1.2) is
  removed. Configure loggable classes with the `#[Loggable]` PHP attribute
  (handled by `AttributeLoggableContextCollectionFactory`) or with XML/YAML
  extractors. The `doctrine/annotations` package is no longer a dependency
  since 1.2.

## Storage abstraction

Core services no longer talk to Elasticsearch directly. Three new interfaces
in `Locastic\Loggastic\Storage` sit between the core and the backend:
`ActivityLogStorageInterface`, `CurrentDataTrackerStorageInterface` and
`StorageInitializerInterface`. The default Elasticsearch implementations live
in `Locastic\Loggastic\Bridge\Elasticsearch\Storage` and are aliased to the
interfaces, so nothing changes unless you extended the affected classes:

- The constructor signatures of `ActivityLogProcessor`, `ActivityLogProvider`,
  `CurrentDataTrackerProvider`, `PopulateCurrentDataTrackersHandler`,
  `CreateLoggableIndexesCommand` and `PopulateCurrentDataTrackersCommand`
  changed: they now receive the storage interfaces instead of
  `ElasticsearchServiceInterface`, `ElasticsearchContextFactoryInterface` and
  `ElasticsearchIndexFactoryInterface`. Update any decorators or service
  definitions overriding their arguments.
- `ActivityLogProviderInterface::getActivityLogsByIndexAndId()` was removed
  from the interface because it leaks the Elasticsearch index name into the
  storage-agnostic API. Inject
  `Locastic\Loggastic\Bridge\Elasticsearch\Storage\ElasticsearchActivityLogStorage`
  and call `findByIndexAndObjectId()` instead.
- `ActivityLogProvider::getCurrentDataTrackerByClassAndId()` was removed. It
  declared an `?array` return type while the denormalizer returns an object,
  so every call that found a tracker threw a `TypeError`. Use
  `CurrentDataTrackerProviderInterface::getCurrentDataTrackerByClassAndId()`.
- `Locastic\Loggastic\Bridge\Elasticsearch\Context\Traits\ElasticNormalizationContextTrait`
  moved to `Locastic\Loggastic\Serializer\Traits\NormalizationContextTrait`;
  it only forces the `\DateTime::ATOM` date format and is not
  Elasticsearch-specific. Update the import if you used it.
- The unused `Locastic\Loggastic\Exception\IndexNotFoundException` class was
  removed.
