<?php

namespace Locastic\ActivityLog\DataProvider;

interface ActivityLogProviderInterface
{
    public function getActivityLogsByClass(string $className): array;
    public function getActivityLogsByClassAndId(string $className, $objectId): array;
}
