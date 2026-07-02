<?php

namespace Locastic\Loggastic\Bridge\Elasticsearch;

interface ElasticsearchServiceInterface
{
    public function createItem($item, string $index, array $groups = []): void;

    public function bulkCreate(array $items, string $index, array $groups = []): void;

    public function getItemById($id, string $index, string $denormalizeToClass): mixed;

    public function getItemByQuery(string $index, string $denormalizeToClass, array $body = []): mixed;

    public function updateItem($id, $item, string $index, array $groups = []): void;

    public function getCollection(string $index, string $denormalizeToClass, array $body = [], $limit = 20, $offset = 0): array;
}
