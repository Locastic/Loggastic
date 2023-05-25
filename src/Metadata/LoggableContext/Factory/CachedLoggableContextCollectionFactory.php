<?php

namespace Locastic\Loggastic\Metadata\LoggableContext\Factory;

use Locastic\Loggastic\Metadata\LoggableContext\LoggableContextCollection;
use Symfony\Contracts\Cache\CacheInterface;

class CachedLoggableContextCollectionFactory implements LoggableContextCollectionFactoryInterface
{
    public const CACHE_KEY = 'loggable_name_collection';

    private CacheInterface $cache;
    private LoggableContextCollectionFactoryInterface $decorated;

    public function __construct(LoggableContextCollectionFactoryInterface $decorated, CacheInterface $cache)
    {
        $this->cache = $cache;
        $this->decorated = $decorated;
    }

    /**
     * {@inheritdoc}
     */
    public function create(): LoggableContextCollection
    {
        return $this->cache->get(self::CACHE_KEY, function () {
            return $this->decorated->create();
        });
    }
}
