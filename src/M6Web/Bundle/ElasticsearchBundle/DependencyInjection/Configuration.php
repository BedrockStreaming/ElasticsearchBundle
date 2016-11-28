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
                        //@deprecated
                        ->scalarNode('client_class')->defaultValue('Elasticsearch\Client')->end()
                        ->scalarNode('clientBuilderClass')->defaultValue('Elasticsearch\ClientBuilder')->end()
                        ->scalarNode('connectionPoolClass')->end()
                        ->scalarNode('selectorClass')->end()
                        ->integerNode('retries')->end()
                        ->scalarNode('logger')->end()
                        ->variableNode('headers')->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
