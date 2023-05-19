<?php

namespace Locastic\Loggastic\DependencyInjection;

use Locastic\Loggastic\Metadata\Extractor\XmlLoggableExtractor;
use Locastic\Loggastic\Metadata\Extractor\YamlLoggableExtractor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\DirectoryResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Finder\Finder;

class LocasticActivityLogExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader->load('commands.yaml');
        $loader->load('identifier.yaml');
        $loader->load('listeners.yaml');
        $loader->load('logger.yaml');
        $loader->load('message_handlers.yaml');
        $loader->load('serializer.yaml');

        $container->setParameter('locastic_activity_log.elasticsearch_host', $config['elastic_host']);
        $container->setParameter('locastic_activity_log.elastic_date_detection', $config['elastic_date_detection']);
        $container->setParameter('locastic_activity_log.elastic_dynamic_date_formats', $config['elastic_dynamic_date_formats']);

        $container->setParameter('locastic_activity_log.activity_log.elastic_properties', $config['activity_log']['elastic_properties']);
        $container->setParameter('locastic_activity_log.current_data_tracker.elastic_properties', $config['current_data_tracker']['elastic_properties']);

        $loader->load('elastic.yaml');

        // load loggable resources
        $loggableClasses = $this->getLoggablePaths($container, $config);

        $loader->load('loggable_context.yaml');
        $container->setParameter('locastic_activity_log.dir.loggable_classes', $loggableClasses['dir']);

        $loader->load('metadata.yaml');
        $container->getDefinition(XmlLoggableExtractor::class)->replaceArgument(0, $loggableClasses['xml']);
        $container->getDefinition(YamlLoggableExtractor::class)->replaceArgument(0, $loggableClasses['yml']);
    }

    private function getLoggablePaths(ContainerBuilder $container, array $config): array
    {
        $loggableClasses = ['yml' => [], 'xml' => [], 'dir' => []];

        if (!array_key_exists('loggable_paths', $config)) {
            return $loggableClasses;
        }

        $loggablePaths = $config['loggable_paths'];

        foreach ($loggablePaths as $path) {
            if (is_dir($path)) {
                foreach (Finder::create()->followLinks()->files()->in($path)->name('/\.(xml|ya?ml)$/')->sortByName() as $file) {
                    $loggableClasses['yaml' === ($extension = $file->getExtension()) ? 'yml' : $extension][] = $file->getRealPath();
                }

                $loggableClasses['dir'][] = $path;
                $container->addResource(new DirectoryResource($path, '/\.(xml|ya?ml|php)$/'));

                continue;
            }

            if ($container->fileExists($path, false)) {
                if (!preg_match('/\.(xml|ya?ml)$/', $path, $matches)) {
                    throw new RuntimeException(sprintf('Unsupported mapping type in "%s", supported types are XML & YAML.', $path));
                }

                $loggableClasses['yaml' === $matches[1] ? 'yml' : $matches[1]][] = $path;

                continue;
            }

            throw new RuntimeException(sprintf('Could not open file or directory "%s".', $path));
        }

        $container->setParameter('locastic_activity_logs.loggable_class_class_directories', $loggableClasses['dir']);

        return $loggableClasses;
    }
}
