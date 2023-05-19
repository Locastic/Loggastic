<?php

namespace Locastic\ActivityLogs\Serializer;

use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

interface ActivityLogCollectionNormalizerInterface extends NormalizerInterface, CacheableSupportsMethodInterface
{
}
