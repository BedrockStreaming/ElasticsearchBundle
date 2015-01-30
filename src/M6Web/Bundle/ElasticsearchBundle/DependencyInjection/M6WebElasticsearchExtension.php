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

        $clientClass = isset($config['client_class']) ? $config['client_class'] : 'Elasticsearch\Client';

        if (isset($config['clients'])) {

            foreach ($config['clients'] as $clientName => $clientConfig) {
                $this->createElasticsearchClient($container, $clientClass, $clientName, $clientConfig);
            }

            if (isset($config['default_client'])) {
                $container->setAlias('m6web_elasticsearch.client.default', sprintf('m6web_elasticsearch.client.%s', $config['default_client']));
            }
        }

    }

    /**
     * Add a new Elasticsearch client definition in the container
     *
     * @param ContainerBuilder $container
     * @param string           $class
     * @param string           $name
     * @param array            $config
     */
    protected function createElasticsearchClient(ContainerBuilder $container, $class, $name, $config)
    {
        $config = $this->convertServiceNamesToReferences($config);

        if ($container->getParameter('kernel.debug')) {
            $logger                = new Reference('m6web_elasticsearch.logger');
            $config['logging']     = true;
            $config['logObject']   = $logger;
            $config['traceObject'] = $logger;
        }

        if (!isset($config['connectionClass']) || $config['connectionClass'] === '\Elasticsearch\Connections\GuzzleConnection') {
            $config['connectionClass'] = '\M6Web\Bundle\ElasticsearchBundle\Connection\GuzzleConnectionDecorator';
        }
        if (isset($config['connectionClass']) && $config['connectionClass'] === '\Elasticsearch\Connections\CurlMultiConnection') {
            $config['connectionClass'] = '\M6Web\Bundle\ElasticsearchBundle\Connection\CurlMultiConnectionDecorator';
        }
        $config['connectionParams']['event_dispatcher'] = new Reference('event_dispatcher');

        $definition = new Definition($class);
        $definition->setArguments([$config]);
        $container->setDefinition('m6web_elasticsearch.client.'.$name, $definition);
    }


    /**
     * @return string
     */
    public function getAlias()
    {
        return 'm6web_elasticsearch';
    }


    /**
     * Convert the service names in the $config array to References
     *
     * @param array $config
     *
     * @return array
     */
    protected function convertServiceNamesToReferences($config)
    {
        // We allow a service name to be set in the `logObject` and `traceObject` configuration
        if (isset($config['logObject']) && is_string($config['logObject'])) {
            $config['logObject'] = new Reference($config['logObject']);
        }
        if (isset($config['traceObject']) && is_string($config['traceObject'])) {
            $config['traceObject'] = new Reference($config['traceObject']);
        }

        return $config;
    }

}
