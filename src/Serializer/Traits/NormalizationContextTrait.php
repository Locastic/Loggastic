<?php

namespace Locastic\Loggastic\Serializer\Traits;

use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

trait NormalizationContextTrait
{
    private function getNormalizationContext(array $context): array
    {
        $context[DateTimeNormalizer::FORMAT_KEY] = \DateTime::ATOM;

        return $context;
    }
}
