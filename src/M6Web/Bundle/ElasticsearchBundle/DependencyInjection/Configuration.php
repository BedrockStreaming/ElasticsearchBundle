<?php

namespace M6Web\Bundle\ElasticsearchBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('m6web_elasticsearch');

        $rootNode
            ->children()
                ->scalarNode('default_client')->end()
                ->scalarNode('client_class')->end()
                ->arrayNode('clients')
                    ->useAttributeAsKey('id')
                    ->prototype('array')
                    ->children()
                        ->arrayNode('hosts')
                            ->isRequired()
                            ->requiresAtLeastOneElement()
                            ->performNoDeepMerging()
                            ->prototype('scalar')->end()
                        ->end()
                        ->scalarNode('connectionClass')->end()
                        ->scalarNode('connectionFactoryClass')->end()
                        ->scalarNode('connectionPoolClass')->end()
                        ->scalarNode('selectorClass')->end()
                        ->scalarNode('serializerClass')->end()
                        ->booleanNode('sniffOnStart')->end()
                        ->variableNode('connectionParams')->end()
                        ->booleanNode('logging')->end()
                        ->scalarNode('logObject')->end()
                        ->scalarNode('logPath')->end()
                        ->scalarNode('logLevel')->end()
                        ->scalarNode('traceObject')->end()
                        ->scalarNode('tracePath')->end()
                        ->scalarNode('traceLevel')->end()
                        ->variableNode('guzzleOptions')->end()
                        ->variableNode('connectionPoolParams')->end()
                        ->integerNode('retries')->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }


}
