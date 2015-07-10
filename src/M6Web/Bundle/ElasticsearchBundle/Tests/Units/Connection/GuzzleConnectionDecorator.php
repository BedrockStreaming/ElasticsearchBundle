<?php

namespace M6Web\Bundle\ElasticsearchBundle\Tests\Units\Connection;

use Guzzle\Http\Client;
use Guzzle\Http\ClientInterface;
use Guzzle\Http\Message\Request;
use Guzzle\Http\Message\Response;
use M6Web\Bundle\ElasticsearchBundle\Connection\GuzzleConnectionDecorator as TestedClass;
use M6Web\Bundle\ElasticsearchBundle\EventDispatcher\ElasticsearchEvent;
use mageekguy\atoum\test;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * GuzzleConnectionDecorator
 */
class GuzzleConnectionDecorator extends test
{
    /**
     * testEventDispatch data provider
     *
     * @return array
     */
    public function eventDispatchDataProvider()
    {
        return [
            [
                'method'             => 'POST',
                'response'           => (new Response(200))->setInfo(['total_time' => 10])->setBody('{"took":54}'),
                'expectedMethod'     => 'POST',
                'expectedStatusCode' => 200,
                'expectedDuration'   => 10000,
                'expectedTook'       => 54
            ],
            [
                'method'             => 'GET',
                'response'           => (new Response(404))->setInfo(['total_time' => 9090])->setBody('{"foo":"bar"}'),
                'expectedMethod'     => 'GET',
                'expectedStatusCode' => 404,
                'expectedDuration'   => 9090000,
                'expectedTook'       => null
            ],
            [
                'method'             => 'GET',
                'response'           => (new Response(404))->setInfo(['total_time' => 0])->setBody('{"took":"bar"}'),
                'expectedMethod'     => 'GET',
                'expectedStatusCode' => 404,
                'expectedDuration'   => 0,
                'expectedTook'       => null
            ]
        ];
    }

    /**
     * Test event dispatch
     *
     * @param string   $method
     * @param Response $response
     * @param string   $expectedMethod
     * @param int      $expectedStatusCode
     * @param int      $expectedDuration
     * @param int      $expectedTook
     *
     * @dataProvider eventDispatchDataProvider
     */
    public function testEventDispatch(
        $method,
        Response $response,
        $expectedMethod,
        $expectedStatusCode,
        $expectedDuration,
        $expectedTook
    )
    {
        $logger = new \mock\Psr\Log\LoggerInterface;
        $client = $this->createClient($response);

        $hostDetails = [
            'scheme' => 'http',
            'host'   => 'localhost',
            'port'   => 9200
        ];

        /** @var ElasticsearchEvent $lastEvent */
        $lastEvent = null;

        /** @var EventDispatcherInterface $eventDispatcher */
        $eventDispatcher = new \mock\Symfony\Component\EventDispatcher\EventDispatcherInterface;;
        $eventDispatcher->getMockController()->dispatch = function ($eventName, $event) use (&$lastEvent) {
            $lastEvent = $event;
        };

        $connectionParams = [
            'guzzleClient'     => $client,
            'event_dispatcher' => $eventDispatcher,
        ];

        $this
            ->if($connection = new TestedClass($hostDetails, $connectionParams, $logger, $logger))
            ->then($connection->performRequest($method, '/foo'))
                ->mock($eventDispatcher)
                    ->call('dispatch')
                    ->withArguments('m6web.elasticsearch', $lastEvent)
                    ->once()
                ->string($lastEvent->getMethod())
                    ->isEqualTo($expectedMethod)
                ->integer($lastEvent->getStatusCode())
                    ->isEqualTo($expectedStatusCode)
                ->integer($lastEvent->getDuration())
                    ->isEqualTo($expectedDuration);

        if (null !== $expectedTook) {
            $this
                ->integer($lastEvent->getTook())
                    ->isEqualTo($expectedTook);
        } else {
            $this
                ->variable($lastEvent->getTook())
                    ->isNull();
        }
    }

    /**
     * Returns a mock client for a POST request
     *
     * @param Response $response
     *
     * @return Client
     */
    protected function createClient(Response $response)
    {
        $createRequest = function ($method, $url, ClientInterface $client, Response $response) {
            $request = new \mock\Guzzle\Http\Message\Request($method, $url);
            $request->setClient($client);

            $request->getMockController()->getResponse = $response;

            return $request;
        };

        $client = new \mock\Guzzle\Http\ClientInterface;

        $client->getMockController()->post = function ($url) use ($client, $response, $createRequest) {

            return $createRequest('POST', $url, $client, $response);
        };

        $client->getMockController()->get = function ($url) use ($client, $response, $createRequest) {

            return $createRequest('GET', $url, $client, $response);
        };

        return $client;
    }
}
