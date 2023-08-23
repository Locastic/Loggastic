<?php

namespace Locastic\Loggastic\Annotation;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class Loggable
{
    public array $groups = [];

    public function __construct(array $groups)
    {
        $this->groups = $groups;
    }

    public function getGroups(): array
    {
        return $this->groups;
    }
}
