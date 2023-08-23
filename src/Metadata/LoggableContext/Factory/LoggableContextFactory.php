<?php

namespace Locastic\Loggastic\Metadata\LoggableContext\Factory;

final class LoggableContextFactory implements LoggableContextFactoryInterface
{
    public function __construct(private readonly LoggableContextCollectionFactoryInterface $loggableContextCollectionFactory)
    {
    }

    public function create(string $loggableClass): ?array
    {
        $loggableContextCollection = $this->loggableContextCollectionFactory->create();

        return $loggableContextCollection->getByClass($loggableClass);
    }
}
