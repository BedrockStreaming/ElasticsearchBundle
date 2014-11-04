<?php

namespace M6Web\Bundle\ElasticsearchBundle\DataCollector;

use M6Web\Bundle\ElasticsearchBundle\Logger\ElasticsearchLogger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * ElasticsearchDataCollector
 */
class ElasticsearchDataCollector extends DataCollector
{
    /**
     * Logger
     *
     * @var ElasticsearchLogger
     */
    private $logger;

    /**
     * Constructor
     *
     * @param ElasticsearchLogger $logger
     */
    public function __construct(ElasticsearchLogger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $this->data['queries'] = $this->logger->getQueries();

        $this->data['total_execution_time'] = 0;
        foreach ($this->data['queries'] as $query) {
            $this->data['total_execution_time'] += $query['duration'];
        }
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

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'elasticsearch';
    }
}
