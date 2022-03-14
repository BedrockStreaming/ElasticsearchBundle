# ElasticsearchBundle [![Build Status](https://github.com/BedrockStreaming/ElasticsearchBundle/actions/workflows/ci.yml/badge.svg)](https://github.com/BedrockStreaming/ElasticsearchBundle/actions/workflows/ci.yml) [![Total Downloads](https://poser.pugx.org/m6web/elasticsearch-bundle/downloads.svg)](https://packagist.org/packages/m6web/elasticsearch-bundle) [![License](http://poser.pugx.org/m6web/elasticsearch-bundle/license)](https://packagist.org/packages/m6web/elasticsearch-bundle) [![PHP Version Require](http://poser.pugx.org/m6web/elasticsearch-bundle/require/php)](https://packagist.org/packages/m6web/elasticsearch-bundle)


Integration of the [Elasticsearch official PHP client](http://github.com/elasticsearch/elasticsearch-php) within a Symfony Project.

## Features

This bundle creates one or more Elasticsearch client services from settings defined in the application configuration.

## Usage

### Installation

You must first add the bundle to your `composer.json`:

```json
    "require": {
        "m6web/elasticsearch-bundle": "dev-master"
    }
```

Then register the bundle in your `AppKernel` class:

```php
<?php
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new M6Web\Bundle\ElasticsearchBundle\M6WebElasticsearchBundle(),
        );
        // ...
    }
```


### Configuration

In your `config.yml`, you can configure one or more Elasticsearch clients:

``` yml
m6web_elasticsearch:
    default_client: my_client
    clients:
        my_client:
            hosts:
                - 'localhost:9200'
                - 'http://other_host:9201'
        my_other_client:
            hosts:
                - 'other_server:9200'
```

From this configuration, the bundle will create two services : 

- `m6web_elasticsearch.client.my_client` that will connect to two Elasticsearch instances: `localhost` on port 9200 and `other_host` on port 9201
- `m6web_elasticsearch.client.my_other_client` that will connect to one Elasticsearch instances: `other_server` on port 9200

It will also create `m6web_elasticsearch.client.default` which is an alias for `m6web_elasticsearch.client.my_client` 

### Additional configuration

Each client can have additional configuration parameters that will be used to instantiate the `\Elasticsearch\Client`. Ex:

``` yml
m6web_elasticsearch:
    clients:
        my_client:
            hosts:
                - 'https://username:password@localhost:9200'
            headers:
                'Accept-Encoding': ['gzip']
            retries: 2
            logger: monolog.logger.custom
            connectionPoolClass: '\Elasticsearch\ConnectionPool\StaticConnectionPool'
            selectorClass: '\Elasticsearch\ConnectionPool\Selectors\RandomSelector'
            connectionParams:
                client:
                    timeout: 3
                    connect_timeout: 1
```


### Events

The bundle dispatches `\M6Web\Bundle\ElasticsearchBundle\EventDispatcher\ElasticsearchEvent` events containing various information about the Elasticsearch requests. 

Events are fired with the name `m6web.elasticsearch`. 

## Tests

You can launch the unit tests using: 

```
./vendor/bin/atoum
```

## License

ElasticsearchBundle is licensed under the [MIT license](LICENSE).
