<?php

namespace Locastic\ActivityLog\Metadata\LoggableContext\Factory;

/**
 * Creates loggable context for a single loggable class
 */
interface LoggableContextFactoryInterface
{
    public function create(string $loggableClass): ?array;
}
