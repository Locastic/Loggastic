<?php

namespace Locastic\Loggastic\Serializer;

use Doctrine\Common\Collections\Collection;
use Locastic\Loggastic\Identifier\LogIdentifierExtractorInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

// Use identifier as collection index
final class ActivityLogCollectionNormalizer implements ActivityLogCollectionNormalizerInterface
{
    public const FORMAT = 'activityLog';

    private NormalizerInterface $decorated;
    private LogIdentifierExtractorInterface $logIdentifierExtractor;

    public function __construct(NormalizerInterface $decorated, LogIdentifierExtractorInterface $logIdentifierExtractor)
    {
        $this->decorated = $decorated;
        $this->logIdentifierExtractor = $logIdentifierExtractor;
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

    public function supportsNormalization($data, string $format = null): bool
    {
        return $data instanceof Collection && self::FORMAT === $format;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return $this->decorated instanceof CacheableSupportsMethodInterface && $this->decorated->hasCacheableSupportsMethod();
    }
}
