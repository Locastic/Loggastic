<?php

namespace Locastic\Loggastic\Metadata\LoggableContext\Factory;

use Locastic\Loggastic\Metadata\LoggableContext\LoggableContextCollection;

/**
 * Creates loggable context collection for all loggable class.
 */
interface LoggableContextCollectionFactoryInterface
{
    public function create(): LoggableContextCollection;
}
