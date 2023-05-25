<?php

namespace Locastic\Loggastic\Metadata\LoggableContext\Factory;

class LoggableContextFactory implements LoggableContextFactoryInterface
{
    private LoggableContextCollectionFactoryInterface $loggableContextCollectionFactory;

    public function __construct(LoggableContextCollectionFactoryInterface $loggableContextCollectionFactory)
    {
        $this->loggableContextCollectionFactory = $loggableContextCollectionFactory;
    }

    public function create(string $loggableClass): ?array
    {
        $loggableContextCollection = $this->loggableContextCollectionFactory->create();

        return $loggableContextCollection->getByClass($loggableClass);
    }
}
