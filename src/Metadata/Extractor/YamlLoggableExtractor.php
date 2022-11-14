<?php

namespace Locastic\ActivityLog\Metadata\Extractor;

use ApiPlatform\Exception\InvalidArgumentException;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class YamlLoggableExtractor extends AbstractLoggableExtractor
{
    protected function extractPath(string $path): void
    {
        try {
            $loggableClasses = Yaml::parse((string)file_get_contents($path), Yaml::PARSE_CONSTANT);
        } catch (ParseException $e) {
            $e->setParsedFile($path);

            throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }

        if (null === $loggableClasses = $loggableClasses['locastic_loggable'] ?? $loggableClasses) {
            return;
        }

        if (!\is_array($loggableClasses)) {
            throw new InvalidArgumentException(sprintf('"locastic_loggable" setting is expected to be null or an array, %s given in "%s".', \gettype($loggableClasses), $path));
        }

        $this->buildLoggableClasses($loggableClasses, $path);
    }

    private function buildLoggableClasses(array $loggableClasses, $path): void
    {
        foreach ($loggableClasses as $loggableClassConfig) {
            $this->validateLoggableClass($loggableClassConfig, $path);
            $this->loggableClasses[$loggableClassConfig['class']] = ['groups' => $loggableClassConfig['groups']];
        }
    }

    private function validateLoggableClass(array $loggableClassConfig, $path): void
    {
        if (!array_key_exists('class', $loggableClassConfig) || !array_key_exists('groups', $loggableClassConfig)) {
            throw new InvalidArgumentException(sprintf('Invalid configuration for "locastic_loggable" in "%s".', $path));
        }

        if (!is_array($loggableClassConfig['groups'])) {
            throw new InvalidArgumentException(sprintf('Invalid configuration for "locastic_loggable": "groups" must be an array, %s given in "%s".', \gettype($loggableClass['groups']), $path));
        }

        if (!class_exists($loggableClassConfig['class'])) {
            throw new InvalidArgumentException(sprintf('Invalid configuration for "locastic_loggable" in "%s": class "%s" does not exist.', $path, $loggableClass['class']));
        }
    }
}
