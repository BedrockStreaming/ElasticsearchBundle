<?php

declare(strict_types=1);

namespace M6Web\Bundle\ElasticsearchBundle\Handler;

use GuzzleHttp\Ring\Core;
use M6Web\Bundle\ElasticsearchBundle\EventDispatcher\ElasticsearchEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * EventHandler
 */
class EventHandler
{
    /**
     * Event dispatcher
     *
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * CURL handler
     *
     * @var callable
     */
    private $handler;

    /**
     * Constructor
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, callable $handler)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->handler = $handler;
    }

    /**
     * Invoke
     *
     * @return \GuzzleHttp\Ring\Future\FutureArray
     */
    public function __invoke(array $request)
    {
        $handler = $this->handler;

        $wrapper = $this;
        $getTook = function (array $response) use ($wrapper) {
            return $wrapper->extractTookFromResponse($response);
        };

        $dispatchEvent = function ($response) use ($request, $getTook) {
            $event = (new ElasticsearchEvent())
                ->setUri($request['uri'])
                ->setMethod($request['http_method'])
                ->setStatusCode($response['status'])
                ->setDuration($response['transfer_stats']['total_time'] * 1000)
                ->setTook($getTook($response));

            if (isset($request['body'])) {
                $event->setBody($request['body']);
            }
            if (isset($request['headers'])) {
                $event->setHeaders($request['headers']);
            }
            if (isset($response['error'])) {
                $event->setError($response['error']->getMessage());
            }

            $this->eventDispatcher->dispatch($event, 'm6web.elasticsearch');

            return $response;
        };

        return Core::proxy($handler($request), $dispatchEvent);
    }

    /**
     * Extract took from response
     *
     * @return int|null
     */
    protected function extractTookFromResponse(array $response)
    {
        if (is_null($response['body'])) {
            return null;
        }
        $content = stream_get_contents($response['body']);
        rewind($response['body']);

        if (preg_match('/\"took\":(\d+)/', $content, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }
}
