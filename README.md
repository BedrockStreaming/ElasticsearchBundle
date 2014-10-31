# ElasticsearchBundle [![Build Status](https://travis-ci.org/M6Web/ElasticsearchBundle.png?branch=master)](https://travis-ci.org/M6Web/ElasticsearchBundle)

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

Each client can have additional configuration parameters that will be used to instanciate the `\Elasticsearch\Client`. Ex:

``` yml
m6web_elasticsearch:
    clients:
        my_client:
            hosts:
                - 'localhost:9200'
            connectionPoolClass: '\Elasticsearch\ConnectionPool\StaticNoPingConnectionPool'
            logging: true
            logLevel: warning
            connectionParams: 
                auth:
                    - username
                    - password
                    - Basic
```

For the `logObject` and `traceObject` parameters, you must specify a service name. Example for using the `my_logger` service:
``` yml
m6web_elasticsearch:
    clients:
        my_logged_client:
            hosts:
                - 'localhost:9200'
            logging: true
            logObject: my_logger
            logLevel: warning
```

## Tests

You can launch the unit tests using: 

```
./vendor/bin/atoum
```

## Roadmap

- Dispatch some events to the Symfony2 eventDispatcher
- Integrate debugging/profiling informations to the Symfony2 Web Debug Toolbar

