<?php

declare(strict_types=1);

namespace Locastic\Loggastic;

use Locastic\Loggastic\Metadata\Extractor\XmlLoggableExtractor;
use Locastic\Loggastic\Metadata\Extractor\YamlLoggableExtractor;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\Config\Resource\DirectoryResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class LocasticLoggasticBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('../config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/commands.yaml');
        $container->import('../config/identifier.yaml');
        $container->import('../config/listeners.yaml');
        $container->import('../config/logger.yaml');
        $container->import('../config/message_dispatcher.yaml');
        $container->import('../config/message_handlers.yaml');
        $container->import('../config/serializer.yaml');

        $builder->setParameter('locastic_activity_log.identifier_extractor', $config['identifier_extractor'] ?? true);

        $this->loadStorage($config, $container, $builder);

        // load loggable resources
        $loggableClasses = $this->getLoggablePaths($builder, $config);

        $container->import('../config/loggable_context.yaml');
        $builder->setParameter('locastic_activity_log.dir.loggable_classes', $loggableClasses['dir']);

        $container->import('../config/metadata.yaml');
        $builder->getDefinition(XmlLoggableExtractor::class)->replaceArgument(0, $loggableClasses['xml']);
        $builder->getDefinition(YamlLoggableExtractor::class)->replaceArgument(0, $loggableClasses['yml']);

        if ($config['default_doctrine_subscriber']) {
            $container->import('../config/activity_log_doctrine_subscriber.yaml');
        }
    }

    /**
     * @param array<array-key, mixed> $config
     */
    private function loadStorage(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        switch ($config['storage']) {
            case 'doctrine':
                $container->import('../config/storage_doctrine.yaml');

                return;
            case 'in_memory':
                $container->import('../config/storage_in_memory.yaml');

                return;
        }

        $builder->setParameter('locastic_activity_log.elasticsearch_host', $config['elastic_host']);
        $builder->setParameter('locastic_activity_log.elasticsearch_user', $config['elastic_user']);
        $builder->setParameter('locastic_activity_log.elasticsearch_password', $config['elastic_password']);
        $builder->setParameter('locastic_activity_log.elasticsearch_ssl_verification', $config['elastic_ssl_verification']);
        $builder->setParameter('locastic_activity_log.elastic_date_detection', $config['elastic_date_detection']);
        $builder->setParameter('locastic_activity_log.elastic_dynamic_date_formats', $config['elastic_dynamic_date_formats']);

        $builder->setParameter('locastic_activity_log.activity_log.elastic_properties', $config['activity_log']['elastic_properties']);
        $builder->setParameter('locastic_activity_log.current_data_tracker.elastic_properties', $config['current_data_tracker']['elastic_properties']);

        $container->import('../config/elastic.yaml');
    }

    private function getLoggablePaths(ContainerBuilder $builder, array $config): array
    {
        $loggableClasses = ['yml' => [], 'xml' => [], 'dir' => []];

        if (!array_key_exists('loggable_paths', $config)) {
            return $loggableClasses;
        }

        $loggablePaths = $config['loggable_paths'];

        // add default paths
        $kernelRoot = $builder->getParameter('kernel.project_dir');

        if (is_dir($dir = $kernelRoot.'/Resources/config/loggastic')) {
            $loggablePaths[] = $dir;
        }

        if (is_dir($dir = $kernelRoot.'/src/Entity')) {
            $loggablePaths[] = $dir;
        }

        $loggablePaths = array_unique($loggablePaths);

        foreach ($loggablePaths as $path) {
            if (is_dir($path)) {
                foreach (Finder::create()->followLinks()->files()->in($path)->name('/\.(xml|ya?ml)$/')->sortByName() as $file) {
                    $loggableClasses['yaml' === ($extension = $file->getExtension()) ? 'yml' : $extension][] = $file->getRealPath();
                }

                $loggableClasses['dir'][] = $path;
                $builder->addResource(new DirectoryResource($path, '/\.(xml|ya?ml|php)$/'));

                continue;
            }

            if ($builder->fileExists($path, false)) {
                if (!preg_match('/\.(xml|ya?ml)$/', (string) $path, $matches)) {
                    throw new RuntimeException(sprintf('Unsupported mapping type in "%s", supported types are XML & YAML.', $path));
                }

                $loggableClasses['yaml' === $matches[1] ? 'yml' : $matches[1]][] = $path;

                continue;
            }

            throw new RuntimeException(sprintf('Could not open file or directory "%s".', $path));
        }

        $builder->setParameter('locastic_activity_logs.loggable_class_class_directories', $loggableClasses['dir']);

        return $loggableClasses;
    }
}
