<?php

namespace Locastic\Loggastic\Metadata\Extractor;

use Symfony\Component\Config\Util\XmlUtils;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;

class XmlLoggableExtractor extends AbstractLoggableExtractor
{
    final public const RESOURCE_SCHEMA = __DIR__.'/schema/metadata.xsd';

    protected function extractPath(string $path): void
    {
        try {
            /** @var \SimpleXMLElement $xml */
            $xml = simplexml_import_dom(XmlUtils::loadFile($path, self::RESOURCE_SCHEMA));
        } catch (\InvalidArgumentException $e) {
            throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }

        foreach ($xml->loggable_class as $loggableClassConfig) {
            $loggableClass = (string) $loggableClassConfig['class'];

            $groups = [];
            foreach ($loggableClassConfig->group as $group) {
                $groups[] = (string) $group['name'];
            }

            $this->loggableClasses[$loggableClass] = ['groups' => $groups];
        }
    }
}
