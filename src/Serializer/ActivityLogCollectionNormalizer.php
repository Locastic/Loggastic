<?php

namespace Locastic\Loggastic\Serializer;

use Doctrine\Common\Collections\Collection;
use Locastic\Loggastic\Identifier\LogIdentifierExtractorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

// Use identifier as collection index
final class ActivityLogCollectionNormalizer implements ActivityLogCollectionNormalizerInterface
{
    public const FORMAT = 'activityLog';

    public function __construct(
        private readonly NormalizerInterface             $decorated,
        private readonly LogIdentifierExtractorInterface $logIdentifierExtractor,
        private readonly bool                            $useIdentifierExtractor
    )
    {
    }

    public function normalize($data, string $format = null, array $context = []): array
    {
        $collection = [];

        if ($this->useIdentifierExtractor) {
            foreach ($data as $item) {
                $normalizedItem = $this->decorated->normalize($item, 'array', $context);

                $logId = $this->logIdentifierExtractor->getIdentifierValue($item);
                if ($logId === null) {
                    continue;
                }

                $collection[$logId] = $normalizedItem;
            }
        } else {
            foreach ($data as $item) {
                $normalizedItem = $this->decorated->normalize($item, 'array', $context);
                $collection[] = $normalizedItem;
            }
        }

        return $collection;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof Collection && self::FORMAT === $format;
    }

    public function getSupportedTypes(?string $format): array
    {
        if (self::FORMAT !== $format) {
            return [];
        }

        return [
            Collection::class => true,
            'array' => true,
            'object' => true,
            'Locastic\Loggastic\Model\Output\ActivityLogInterface' => true,
        ];
    }
}
