<?php

namespace Locastic\ActivityLog\Metadata\LoggableContext\Factory;

use Locastic\ActivityLog\Metadata\LoggableContext\LoggableContextCollection;

/**
 * Creates loggable context collection for all loggable class
 */
interface LoggableContextCollectionFactoryInterface
{
    public function create(): LoggableContextCollection;
}
