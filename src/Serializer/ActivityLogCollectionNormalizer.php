<?php

namespace Locastic\Loggastic\Serializer;

use Doctrine\Common\Collections\Collection;
use Locastic\Loggastic\Identifier\LogIdentifierExtractorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

// Use identifier as collection index
final class ActivityLogCollectionNormalizer implements ActivityLogCollectionNormalizerInterface
{
    public const FORMAT = 'activityLog';

    public function __construct(private readonly NormalizerInterface $decorated, private readonly LogIdentifierExtractorInterface $logIdentifierExtractor)
    {
    }

    public function normalize($object, string $format = null, array $context = []): array
    {
        $data = [];
        foreach ($object as $index => $item) {
            $normalizedItem = $this->decorated->normalize($item, 'array', $context);
            $logId = $this->logIdentifierExtractor->getIdentifierValue($item) ?: $index;

            $data[$logId] = $normalizedItem;
        }

        return $data;
    }

    public function getSupportedTypes(?string $format): array
    {
        return $this->decorated->getSupportedTypes($format);
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof Collection && self::FORMAT === $format;
    }
}
