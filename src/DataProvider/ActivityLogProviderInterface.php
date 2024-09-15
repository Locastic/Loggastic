<?php

namespace Locastic\Loggastic\DataProvider;

interface ActivityLogProviderInterface
{
    public function getActivityLogsByClass(string $className, array $sort = [], int $limit = 20, int $offset = 0): array;

    public function getActivityLogsByClassAndId(string $className, $objectId, array $sort = [], int $limit = 20, int $offset = 0): array;

    public function getActivityLogsByIndexAndId(string $index, $objectId, array $sort = [], int $limit = 20, int $offset = 0): array;
}
