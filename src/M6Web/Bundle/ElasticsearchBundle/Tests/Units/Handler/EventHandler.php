<?php

namespace M6Web\Bundle\ElasticsearchBundle\Tests\Units\Handler;

use GuzzleHttp\Ring\Future\CompletedFutureArray;
use GuzzleHttp\Ring\Future\FutureArray;
use M6Web\Bundle\ElasticsearchBundle\EventDispatcher\ElasticsearchEvent;
use M6Web\Bundle\ElasticsearchBundle\Handler\EventHandler as TestedClass;
use atoum\atoum;
use React\Promise\RejectedPromise;

/**
 * EventHandler
 */
class EventHandler extends atoum
{
    /**
     * Test dispatch
     *
     * @param array              $request
     * @param array              $response
     * @param ElasticsearchEvent $expectedEvent
     */
    public function testDispatch(array $request, array $response, ElasticsearchEvent $expectedEvent)
    {
        $eventDispatcher = $this->getEventDispatcher();
        $requestHandler  = $this->getRequestHandler();
        $future          = new CompletedFutureArray($response);

        $this->calling($requestHandler)->__invoke = $future;

        $this
            ->if($handler = new TestedClass($eventDispatcher, $requestHandler))
            ->variable($future = $handler($request))
            ->mock($eventDispatcher)->call('dispatch')->withArguments('m6web.elasticsearch', $expectedEvent)->once();
    }

    /**
     * Test no dispatch
     */
    public function testNoDispatch()
    {
        $request         = [];
        $eventDispatcher = $this->getEventDispatcher();
        $requestHandler  = $this->getRequestHandler();
        $promise         = new RejectedPromise();
        $future          = new FutureArray($promise);

        $this->calling($requestHandler)->__invoke = $future;

        $this
            ->if($handler = new TestedClass($eventDispatcher, $requestHandler))
            ->variable($future = $handler($request))
            ->mock($eventDispatcher)->call('dispatch')->never();
    }

    /**
     * testDispatch data provider
     *
     * @return array
     */
    protected function testDispatchDataProvider()
    {
        return [
            [
                'request'       => ['uri' => '/_search', 'http_method' => 'GET'],
                'response'      => [
                    'transfer_stats' => ['total_time' => 0.5],
                    'status'         => 200,
                    'body'           => fopen('data://text/plain,', 'r'),
                ],
                'expectedEvent' => (new ElasticsearchEvent())
                    ->setUri('/_search')
                    ->setMethod('GET')
                    ->setStatusCode(200)
                    ->setDuration(500)
                    ->setTook(null),
            ],
            [
                'request'       => ['uri' => '/_count', 'http_method' => 'POST'],
                'response'      => [
                    'transfer_stats' => ['total_time' => 1],
                    'status'         => 500,
                    'body'           => fopen('data://text/plain,'.json_encode(['took' => 10]), 'r'),
                ],
                'expectedEvent' => (new ElasticsearchEvent())
                    ->setUri('/_count')
                    ->setMethod('POST')
                    ->setStatusCode(500)
                    ->setDuration(1000)
                    ->setTook(10),
            ],
        ];
    }

    /**
     * Get event dispatcher
     *
     * @return \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected function getEventDispatcher()
    {
        $eventDispatcher = new \mock\Symfony\Component\EventDispatcher\EventDispatcherInterface();

        return $eventDispatcher;
    }

    /**
     * Get request handler
     *
     * @return \GuzzleHttp\Ring\Client\CurlHandler
     */
    protected function getRequestHandler()
    {
        $requestHandler = new \mock\GuzzleHttp\Ring\Client\CurlHandler();

        return $requestHandler;
    }
}
