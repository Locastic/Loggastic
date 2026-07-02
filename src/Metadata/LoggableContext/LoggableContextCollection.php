<?php

namespace Locastic\Loggastic\Metadata\LoggableContext;

final class LoggableContextCollection implements \IteratorAggregate, \Countable
{
    public function __construct(private readonly array $loggableContextCollection = [])
    {
    }

    public function getByClass(string $loggableClass): ?array
    {
        return $this->getIterator()[$loggableClass] ?? null;
    }

    /**
     * @return \Traversable<string>
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->loggableContextCollection);
    }

    public function count(): int
    {
        return \count($this->loggableContextCollection);
    }
}
