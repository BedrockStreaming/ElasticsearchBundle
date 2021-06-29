<?php

namespace M6Web\Bundle\ElasticsearchBundle\Handler;

use GuzzleHttp\Ring\Core;
use GuzzleHttp\Ring\Future\FutureInterface;

/**
 * HeadersHandler
 */
class HeadersHandler
{
    /**
     * Headers
     *
     * @var array
     */
    private $headers = [];

    /**
     * Request handler
     *
     * @var callable
     */
    private $handler;

    /**
     * Constructor
     *
     * @param callable $handler
     */
    public function __construct(callable $handler)
    {
        $this->handler = $handler;
    }

    /**
     * Set header
     *
     * @param string $key
     * @param string $value
     *
     * @return $this
     */
    public function setHeader($key, $value)
    {
        $this->headers[$key] = $value;

        return $this;
    }

    /**
     * Invoke
     *
     * @param array $request
     *
     * @return FutureInterface
     */
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
