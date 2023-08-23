<?php

namespace Locastic\Loggastic\Bridge\Elasticsearch\Context\Traits;

use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

trait ElasticNormalizationContextTrait
{
    private function getNormalizationContext(array $context): array
    {
        $context[DateTimeNormalizer::FORMAT_KEY] = \DateTime::ATOM;

        return $context;
    }
}
