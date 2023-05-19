<?php

namespace Locastic\ActivityLogs\Bridge\Elasticsearch\Context\Traits;

use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

trait ElasticNormalizationContextTrait
{
    private function getNormalizationContext(array $context): array
    {
        return [
            'groups' => $context['groups'] ?? [],
            DateTimeNormalizer::FORMAT_KEY => \DateTime::ATOM, //todo move to config
        ];
    }
}
