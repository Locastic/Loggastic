<?php

declare(strict_types=1);

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

return static function (DefinitionConfigurator $definition): void {
    $activityLogProperties = [
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

    $currentDataTrackerProperties = [
        'dateTime' => ['type' => 'date'],
        'objectId' => ['type' => 'text'],
        'objectType' => ['type' => 'text'],
        'objectClass' => ['type' => 'text'],
        'data' => ['type' => 'text'],
    ];

    $definition->rootNode()
        ->children()
            ->enumNode('storage')
                ->info('Storage backend for activity logs and current data trackers.')
                ->values(['elasticsearch', 'doctrine', 'in_memory'])
                ->defaultValue('elasticsearch')
            ->end()
            ->booleanNode('default_doctrine_subscriber')
                ->defaultTrue()
            ->end()
            ->booleanNode('identifier_extractor')
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
            ->scalarNode('elastic_user')
                ->defaultNull()
            ->end()
            ->scalarNode('elastic_password')
                ->defaultNull()
            ->end()
            ->booleanNode('elastic_ssl_verification')
                ->defaultTrue()
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
                    ->arrayNode('elastic_properties')
                        ->arrayPrototype()
                            ->validate()
                                ->always(function ($v) {
                                    if (empty($v['properties'])) {
                                        unset($v['properties']);
                                    }

                                    return $v;
                                })
                            ->end()
                            ->children()
                                ->scalarNode('type')->end()
                                ->arrayNode('properties')
                                    ->arrayPrototype()->scalarPrototype()->end()->end()
                                ->end()
                            ->end()
                        ->end()
                        ->defaultValue($activityLogProperties)
                    ->end()
                ->end()
            ->end()
            ->arrayNode('current_data_tracker')
                ->addDefaultsIfNotSet()
                ->children()
                    ->arrayNode('elastic_properties')
                        ->arrayPrototype()
                            ->validate()
                                ->always(function ($v) {
                                    if (empty($v['properties'])) {
                                        unset($v['properties']);
                                    }

                                    return $v;
                                })
                            ->end()
                            ->children()
                                ->scalarNode('type')->end()
                                ->arrayNode('properties')
                                    ->arrayPrototype()->scalarPrototype()->end()->end()
                                ->end()
                            ->end()
                        ->end()
                        ->defaultValue($currentDataTrackerProperties)
                    ->end()
                ->end()
            ->end()
        ->end()
    ;
};
