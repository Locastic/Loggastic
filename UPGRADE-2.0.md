# Upgrading to 2.0

This document collects the breaking changes planned for 2.0. Each entry lists
the deprecation shipped in a 1.x release so you can migrate before upgrading.

## Metadata

- `AnnotationLoggableContextCollectionFactory` (deprecated since 1.2) is
  removed. Configure loggable classes with the `#[Loggable]` PHP attribute
  (handled by `AttributeLoggableContextCollectionFactory`) or with XML/YAML
  extractors. The `doctrine/annotations` package is no longer a dependency
  since 1.2.
