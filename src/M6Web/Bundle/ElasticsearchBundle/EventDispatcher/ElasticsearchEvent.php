<?php

declare(strict_types=1);

namespace M6Web\Bundle\ElasticsearchBundle\EventDispatcher;

use Symfony\Contracts\EventDispatcher\Event;

class ElasticsearchEvent extends Event
{
    /** duration of the Elasticsearch request in milliseconds */
    private ?float $duration = null;

    /** HTTP method */
    private ?string $method = null;

    /** Elasticsearch URI */
    private ?string $uri = null;

    /**  HTTP status code */
    private ?int $statusCode = null;

    /** Time in milliseconds for Elasticsearch to execute the search */
    private ?int $took = null;
    private array $headers = [];

    /** Body of the ES request */
    private string $body = '';
    private string $error = '';

    public function getDuration(): ?float
    {
        return $this->duration;
    }

    public function setDuration(float $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getMethod(): ?string
    {
        return $this->method;
    }

    public function setMethod(?string $method): self
    {
        $this->method = $method;

        return $this;
    }

    public function getUri(): ?string
    {
        return $this->uri;
    }

    public function setUri(?string $uri): self
    {
        $this->uri = $uri;

        return $this;
    }

    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    public function setStatusCode(?int $statusCode): self
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    public function getTook(): ?int
    {
        return $this->took;
    }

    public function setTook(?int $took): self
    {
        $this->took = $took;

        return $this;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function setHeaders(array $headers): self
    {
        $this->headers = $headers;

        return $this;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function setBody(string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function getError(): string
    {
        return $this->error;
    }

    public function setError(string $error): self
    {
        $this->error = $error;

        return $this;
    }
}
