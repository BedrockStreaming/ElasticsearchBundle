<?php

declare(strict_types=1);

namespace M6Web\Bundle\ElasticsearchBundle\Handler;

use GuzzleHttp\Ring\Core;

class HeadersHandler
{
    /** Headers */
    private array $headers = [];

    /**
     * Request handler
     *
     * @var callable
     */
    private $handler;

    /**
     * Constructor
     */
    public function __construct(callable $handler)
    {
        $this->handler = $handler;
    }

    public function setHeader(string $key, $value): self
    {
        $this->headers[$key] = $value;

        return $this;
    }

    public function __invoke(array $request)
    {
        // By default, host is stored in headers and final url is forged later.
        // We need to build url now to handle the case when we want to add a custom Host header.
        $request['url'] = Core::url($request);

        $handler = $this->handler;
        foreach ($this->headers as $key => $value) {
            if ($key == 'Accept-Encoding' && in_array('gzip', $value)) {
                $request['client']['decode_content'] = true;
            }
            $request['headers'][$key] = $value;
        }

        return $handler($request);
    }
}
