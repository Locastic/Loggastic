# Upgrading to 2.0

This document collects the breaking changes planned for 2.0. Each entry lists
the deprecation shipped in a 1.x release so you can migrate before upgrading.

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
