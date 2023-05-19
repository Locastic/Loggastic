<?php

namespace Locastic\ActivityLogs\Serializer;

use Doctrine\Common\Collections\Collection;
use Locastic\ActivityLogs\Identifier\LogIdentifierExtractorInterface;
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
        foreach ($object as $item) {
            $logId = $this->logIdentifierExtractor->getIdentifierValue($item);
            $data[$logId] = $this->decorated->normalize($item, 'array', $context);
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
