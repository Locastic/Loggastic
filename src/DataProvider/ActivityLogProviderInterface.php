<?php

namespace Locastic\Loggastic\DataProvider;

interface ActivityLogProviderInterface
{
    public function getActivityLogsByClass(string $className, array $sort = []): array;

    public function getActivityLogsByClassAndId(string $className, $objectId, array $sort = []): array;

    public function getActivityLogsByIndexAndId(string $index, $objectId, array $sort = []): array;
}
