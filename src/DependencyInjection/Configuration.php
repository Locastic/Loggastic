<?php

namespace Locastic\Loggastic\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('locastic_activity_log');

        if (method_exists($treeBuilder, 'getRootNode')) {
            $rootNode = $treeBuilder->getRootNode();
        } else {
            // BC for symfony/config < 4.2
            $rootNode = $treeBuilder->getRootNode();
        }

        $rootNode
            ->children()
                ->booleanNode('default_doctrine_subscriber')
                    ->defaultTrue()
                ->end()
                ->arrayNode('loggable_classes')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('class')->end()
                            ->arrayNode('groups')
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('elastic_host')
                    ->cannotBeEmpty()
                    ->defaultValue('http://localhost:9200')
                ->end()
                ->arrayNode('loggable_paths')
                    ->prototype('scalar')->end()
                ->end()
                ->booleanNode('elastic_date_detection')
                    ->defaultValue(true)
                ->end()
                ->scalarNode('elastic_dynamic_date_formats')
                    ->cannotBeEmpty()
                    ->defaultValue('strict_date_optional_time||epoch_millis||strict_time')
                ->end()
                ->arrayNode('activity_log')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('elastic_properties')
                            ->defaultValue($this->getActivityLogProperties())
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('current_data_tracker')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('elastic_properties')
                            ->cannotBeEmpty()
                            ->defaultValue($this->getCurrentDataTrackerProperties())
                         ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }

    private function getActivityLogProperties(): array
    {
        return [
            'id' => ['type' => 'keyword'],
            'action' => ['type' => 'text'],
            'loggedAt' => ['type' => 'date'],
            'objectId' => ['type' => 'text'],
            'objectType' => ['type' => 'text'],
            'objectClass' => ['type' => 'text'],
            'dataChanges' => ['type' => 'text'],
            'user' => [
                'type' => 'object',
                'properties' => [
                    'username' => ['type' => 'text'],
                ],
            ],
        ];
    }

    public function getCurrentDataTrackerProperties(): array
    {
        return [
            'dateTime' => ['type' => 'date'],
            'objectId' => ['type' => 'text'],
            'objectType' => ['type' => 'text'],
            'objectClass' => ['type' => 'text'],
            'jsonData' => ['type' => 'text'],
        ];
    }
}
