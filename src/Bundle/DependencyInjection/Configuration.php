<?php
namespace Boekkooi\Broadway\Bundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * @inheritdoc
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode    = $treeBuilder->root('boekkooi_broadway');

        $rootNode
            ->children()
                ->arrayNode('command_handling')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('command_bus')
                            ->defaultValue('default')
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('event_store')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('serializer')
                            ->addDefaultsIfNotSet()
                            ->beforeNormalization()
                                ->ifString()
                                ->then(function($value) {
                                    return [ 'payload' => $value, 'metadata' => $value ];
                                })
                            ->end()
                            ->children()
                                ->scalarNode('payload')
                                    ->defaultNull()
                                ->end()
                                ->scalarNode('metadata')
                                    ->defaultNull()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('read_model')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('serializer')
                            ->defaultNull()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
