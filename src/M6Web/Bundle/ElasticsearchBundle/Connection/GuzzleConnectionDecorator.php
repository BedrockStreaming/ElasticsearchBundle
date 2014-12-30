<?php


namespace M6Web\Bundle\ElasticsearchBundle\Connection;


use Elasticsearch\Common\Exceptions\ElasticsearchException;
use Elasticsearch\Connections\GuzzleConnection;
use M6Web\Bundle\ElasticsearchBundle\EventDispatcher\ElasticsearchEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class GuzzleConnectionDecorator
 * This class decorates GuzzleConnection to allow event dispatching
 */
class GuzzleConnectionDecorator extends GuzzleConnection
{
    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher = null;

    /**
     * {@inheritdoc}
     */
    public function __construct($hostDetails, $connectionParams, LoggerInterface $log, LoggerInterface $trace)
    {
        if (isset($connectionParams['event_dispatcher'])) {
            $this->eventDispatcher = $connectionParams['event_dispatcher'];
        }

        parent::__construct($hostDetails, $connectionParams, $log, $trace);
    }

    /**
     * {@inheritdoc}
     */
    public function performRequest($method, $uri, $params = null, $body = null, $options = array())
    {
        try {
            $response = parent::performRequest($method, $uri, $params, $body, $options);
        } catch (ElasticsearchException $e) {
            $this->dispatchEvent($method, $uri, $e->getCode(), null);
            throw $e;
        }

        $this->dispatchEvent(
            $method,
            $uri,
            $response['status'],
            $response['info']['total_time'] * 1000  // Convert from seconds to milliseconds
        );

        return $response;
    }

    /**
     * Dispatch an event
     *
     * @param string $method
     * @param string $uri
     * @param int    $statusCode
     * @param float  $duration
     */
    protected function dispatchEvent($method, $uri, $statusCode, $duration)
    {
        if ($this->eventDispatcher !== null) {
            $event = new ElasticsearchEvent();
            $event
                ->setUri($uri)
                ->setMethod($method)
                ->setStatusCode($statusCode)
                ->setDuration($duration);

            $this->eventDispatcher->dispatch('m6web.elasticsearch', $event);
        }
    }

}