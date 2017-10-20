<?php

namespace M6Web\Bundle\ElasticsearchBundle\DataCollector;

use M6Web\Bundle\ElasticsearchBundle\EventDispatcher\ElasticsearchEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * ElasticsearchDataCollector
 */
class ElasticsearchDataCollector extends DataCollector
{
    /**
     * ElasticsearchDataCollector constructor.
     */
    public function __construct()
    {
        $this->data = [
            'queries'              => [],
            'total_execution_time' => 0,
        ];
    }


    /**
     * @param ElasticsearchEvent $event
     */
    public function handleEvent(ElasticsearchEvent $event)
    {
        $query = array(
            'method'      => $event->getMethod(),
            'uri'         => $event->getUri(),
            'headers'     => $this->cloneVar($event->getHeaders()),
            'status_code' => $event->getStatusCode(),
            'duration'    => $event->getDuration(),
            'took'        => $event->getTook(),
            'body'        => json_decode($event->getBody()),
            'error'       => $event->getError(),
        );
        $this->data['queries'][] = $query;
        $this->data['total_execution_time'] += $query['duration'];
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'elasticsearch';
    }

    /**
     * Get queries
     *
     * @return array
     */
    public function getQueries()
    {
        return $this->data['queries'];
    }

    /**
     * Get total execution time
     *
     * @return float
     */
    public function getTotalExecutionTime()
    {
        return $this->data['total_execution_time'];
    }

}
