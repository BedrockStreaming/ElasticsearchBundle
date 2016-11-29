<?php

namespace M6Web\Bundle\ElasticsearchBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class M6WebElasticsearchExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        if (isset($config['clients'])) {
            foreach ($config['clients'] as $clientName => $clientConfig) {
                $this->createElasticsearchClient($container, $clientName, $clientConfig);
            }

            if (isset($config['default_client'])) {
                $container->setAlias(
                    'm6web_elasticsearch.client.default',
                    sprintf('m6web_elasticsearch.client.%s', $config['default_client'])
                );
            }
        }
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return 'm6web_elasticsearch';
    }

    /**
     * Add a new Elasticsearch client definition in the container
     *
     * @param ContainerBuilder $container
     * @param string           $name
     * @param array            $config
     */
    protected function createElasticsearchClient(ContainerBuilder $container, $name, $config)
    {
        $definitionId = 'm6web_elasticsearch.client.'.$name;

        $handlerId = $this->createHandler($container, $config, $definitionId);

        $clientConfig = [
            'hosts'   => $config['hosts'],
            'handler' => new Reference($handlerId),
        ];

        if (isset($config['retries'])) {
            $clientConfig['retries'] = $config['retries'];
        }

        if (isset($config['logger'])) {
            $clientConfig['logger'] = new Reference($config['logger']);
        }

        if (!empty($config['connectionPoolClass'])) {
            $clientConfig['connectionPool'] = $config['connectionPoolClass'];
        }

        if (!empty($config['selectorClass'])) {
            $clientConfig['selector'] = $config['selectorClass'];
        }

        if (!empty($config['connectionParams'])) {
            $clientConfig['connectionParams'] = $config['connectionParams'];
        }

        $definition = (new Definition($config['client_class']))
            ->setArguments([$clientConfig]);
        $this->setFactoryToDefinition($config['clientBuilderClass'], 'fromConfig', $definition);

        $container->setDefinition($definitionId, $definition);

        if ($container->getParameter('kernel.debug')) {
            $this->createDataCollector($container);
        }
    }

    /**
     * Create request handler
     *
     * @param ContainerBuilder $container
     * @param array            $config
     * @param string           $definitionId
     *
     * @return string
     */
    protected function createHandler(ContainerBuilder $container, array $config, $definitionId)
    {
        // cURL handler
        $singleHandler   = (new Definition('GuzzleHttp\Ring\Client\CurlHandler'))
            ->setPublic(false);

        $this->setFactoryToDefinition('Elasticsearch\ClientBuilder', 'defaultHandler', $singleHandler);

        $singleHandlerId = $definitionId.'.single_handler';
        $container->setDefinition($singleHandlerId, $singleHandler);

        // Headers handler
        $headersHandler = (new Definition('M6Web\Bundle\ElasticsearchBundle\Handler\HeadersHandler'))
            ->setPublic(false)
            ->setArguments([new Reference($singleHandlerId)]);
        if (isset($config['headers'])) {
            foreach ($config['headers'] as $key => $value) {
                $headersHandler->addMethodCall('setHeader', [$key, $value]);
            }
        }
        $headersHandlerId = $definitionId.'.headers_handler';
        $container->setDefinition($headersHandlerId, $headersHandler);

        // Event handler
        $eventHandler   = (new Definition('M6Web\Bundle\ElasticsearchBundle\Handler\EventHandler'))
            ->setPublic(false)
            ->setArguments([new Reference('event_dispatcher'), new Reference($headersHandlerId)]);
        $eventHandlerId = $definitionId.'.event_handler';
        $container->setDefinition($eventHandlerId, $eventHandler);

        return $eventHandlerId;
    }

    /**
     * @param string     $className
     * @param string     $method
     * @param Definition $definition
     */
    private function setFactoryToDefinition($className, $method, Definition $definition)
    {
        // Symfony 2.3 backward compatibility
        if (method_exists('Symfony\Component\DependencyInjection\Definition', 'setFactory')) {
            $definition->setFactory([$className, $method]);
        } else {
            $definition
                ->setFactoryClass($className)
                ->setFactoryMethod($method);
        }
    }

    /**
     * @param ContainerBuilder $container
     */
    protected function createDataCollector(ContainerBuilder $container)
    {
        $collectorDefinition = new Definition(
            'M6Web\Bundle\ElasticsearchBundle\DataCollector\ElasticsearchDataCollector'
        );
        $collectorDefinition->addTag(
            'data_collector',
            [
                'template' => 'M6WebElasticsearchBundle:Collector:elasticsearch',
                'id'       => 'elasticsearch'
            ]
        );

        $collectorDefinition->addTag(
            'kernel.event_listener',
            [
                'event' => 'm6web.elasticsearch',
                'method' => 'handleEvent'
            ]
        );

        $container->setDefinition('m6web_elasticsearch.data_collector', $collectorDefinition);
    }
}
