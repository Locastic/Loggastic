<?php

namespace Locastic\Loggastic\Metadata\LoggableContext;

class LoggableContextCollection implements \IteratorAggregate, \Countable
{
    private array $loggableContextCollection;

    public function __construct(array $loggableContextCollection = [])
    {
        $this->loggableContextCollection = $loggableContextCollection;
    }

    public function getByClass(string $loggableClass): ?array
    {
        return $this->getIterator()[$loggableClass] ?? null;
    }

    /**
     * {@inheritdoc}
     *
     * @return \Traversable<string>
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->loggableContextCollection);
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        return \count($this->loggableContextCollection);
    }
}
