<?php

namespace Locastic\ActivityLog\Annotation;

use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class Loggable
{
    public array $groups = [];

    public function __construct(array $groups)
    {
        $this->groups = $groups['groups'];
    }

    public function getGroups(): array
    {
        return $this->groups;
    }

    public function getNormalizationContext(): array
    {
        return [
            'groups' => $this->getGroups(),
            DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s' // todo add to normalizer and config
        ];
    }
}
