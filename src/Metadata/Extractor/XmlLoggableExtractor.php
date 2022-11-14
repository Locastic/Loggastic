<?php

namespace Locastic\ActivityLog\Metadata\Extractor;

use ApiPlatform\Core\Exception\InvalidArgumentException;
use Symfony\Component\Config\Util\XmlUtils;

class XmlLoggableExtractor extends AbstractLoggableExtractor
{
    public const RESOURCE_SCHEMA = __DIR__ . '/schema/metadata.xsd';

    protected function extractPath(string $path): void
    {
        try {
            /** @var \SimpleXMLElement $xml */
            $xml = simplexml_import_dom(XmlUtils::loadFile($path, self::RESOURCE_SCHEMA));
        } catch (\InvalidArgumentException $e) {
            throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }

        foreach ($xml->loggable_class as $loggableClassConfig) {
            $loggableClass = (string)$loggableClassConfig['class'];

            $groups = [];
            foreach ($loggableClassConfig->group as $group) {
                $groups[] = (string)$group['name'];
            }

            $this->loggableClasses[$loggableClass] = ['groups' => $groups];
        }
    }
}
