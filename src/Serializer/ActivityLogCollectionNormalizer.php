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

    public function normalize($object, string $format = null, array $context = []): array
    {
        $data = [];

        if ($this->useIdentifierExtractor) {
            foreach ($object as $item) {
                $normalizedItem = $this->decorated->normalize($item, 'array', $context);

                $logId = $this->logIdentifierExtractor->getIdentifierValue($item);
                if ($logId === null) {
                    continue;
                }

                $data[$logId] = $normalizedItem;
            }
        } else {
            foreach ($object as $item) {
                $normalizedItem = $this->decorated->normalize($item, 'array', $context);
                $data[] = $normalizedItem;
            }
        }

        return $data;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof Collection && self::FORMAT === $format;
    }
}
