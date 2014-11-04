<?php

namespace M6Web\Bundle\ElasticsearchBundle\Logger;

use Elasticsearch\Common\EmptyLogger;

/**
 * ElasticsearchLogger
 */
class ElasticsearchLogger extends EmptyLogger
{
    /**
     * Contexts
     *
     * @var array
     */
    private $contexts = array();

    /**
     * Datas
     *
     * @var array
     */
    private $datas = array();

    /**
     * CURL commands
     *
     * @var array
     */
    private $curlCommands = array();

    /**
     * Queries
     *
     * @var array
     */
    private $queries;

    /**
     * {@inheritdoc}
     */
    public function debug($message, array $context = array())
    {
        if ($message == 'Request Body') {
            $this->datas[] = json_decode(current($context));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function info($message, array $context = array())
    {
        if ($message == 'Request Success:') {
            $this->contexts[] = $context;
        } elseif (preg_match('/^curl/', $message)) {
            $this->curlCommands[] = $message;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function warning($message, array $context = array())
    {
        if ($message == 'Request Failure:') {
            $this->contexts[] = $context;
        }
    }

    /**
     * Get queries
     *
     * @return array
     */
    public function getQueries()
    {
        if (null === $this->queries) {
            $this->queries = $this->computeQueries();
        }

        return $this->queries;
    }

    /**
     * Compute queries
     *
     * @return array
     */
    private function computeQueries()
    {
        $queries = array();
        foreach ($this->contexts as $key => $context) {
            $query = array(
                'method'      => $context['method'],
                'uri'         => $context['uri'],
                'headers'     => $context['headers'],
                'status_code' => $context['HTTP code'],
                'duration'    => $context['duration'],
            );
            if (isset($context['error'])) {
                $query['error'] = $context['error'];
            }
            if (isset($this->datas[$key])) {
                $query['data'] = $this->datas[$key];
            }
            if (isset($this->curlCommands[$key])) {
                $query['curl'] = $this->curlCommands[$key];
            }
            $queries[] = $query;
        }

        return $queries;
    }
}
