<?php

namespace Locastic\Loggastic\Metadata\LoggableContext\Factory;

use Locastic\Loggastic\Metadata\LoggableContext\LoggableContextCollection;
use Symfony\Contracts\Cache\CacheInterface;

class CachedLoggableContextCollectionFactory implements LoggableContextCollectionFactoryInterface
{
    final public const CACHE_KEY = 'loggable_name_collection';

    public function __construct(private readonly LoggableContextCollectionFactoryInterface $decorated, private readonly CacheInterface $cache)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function create(): LoggableContextCollection
    {
        return $this->cache->get(self::CACHE_KEY, fn() => $this->decorated->create());
    }
}
