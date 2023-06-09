<?php

namespace Locastic\Loggastic\DataProvider;

interface ActivityLogProviderInterface
{
    public function getActivityLogsByClass(string $className): array;

    public function getActivityLogsByClassAndId(string $className, $objectId): array;

    public function getActivityLogsByIndexAndId(string $index, $objectId, array $sort = []): array;
}
