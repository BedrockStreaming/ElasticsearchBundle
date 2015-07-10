<?php


namespace M6Web\Bundle\ElasticsearchBundle\EventDispatcher;


use Symfony\Component\EventDispatcher\Event;

/**
 * Class ElasticsearchEvent
 *
 */
class ElasticsearchEvent extends Event
{
    /**
     * @var float duration of the Elasticsearch request in milliseconds
     */
    private $duration;

    /**
     * @var string HTTP method
     */
    private $method;

    /**
     * @var string Elasticsearch URI
     */
    private $uri;

    /**
     * @var int HTTP status code
     */
    private $statusCode;

    /**
     * Time in milliseconds for Elasticsearch to execute the search
     *
     * @var int
     */
    private $took;

    /**
     * @return float
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * @param float $duration
     *
     * @return $this
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;

        return $this;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param string $method
     *
     * @return $this
     */
    public function setMethod($method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @param string $uri
     *
     * @return $this
     */
    public function setUri($uri)
    {
        $this->uri = $uri;

        return $this;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @param int $statusCode
     *
     * @return $this
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * Get took
     *
     * @return int
     */
    public function getTook()
    {
        return $this->took;
    }

    /**
     * Set took
     *
     * @param int $took
     *
     * @return $this
     */
    public function setTook($took)
    {
        $this->took = $took;

        return $this;
    }
}
