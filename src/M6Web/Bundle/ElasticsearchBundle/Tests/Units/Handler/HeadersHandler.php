<?php

declare(strict_types=1);

namespace M6Web\Bundle\ElasticsearchBundle\Tests\Units\Handler;

use atoum\atoum;
use M6Web\Bundle\ElasticsearchBundle\Handler\HeadersHandler as TestedClass;

/**
 * HeadersHandler
 */
class HeadersHandler extends atoum
{
    /**
     * Test calling HeadersHandler
     */
    public function testInvoke(array $request, array $expectedRequest, array $headers)
    {
        $requestHandler = $this->getRequestHandler();
        $expectedResult = true;

        $this->calling($requestHandler)->__invoke = $expectedResult;

        $handler = new TestedClass($requestHandler);
        foreach ($headers as $key => $value) {
            $handler->setHeader($key, $value);
        }

        $this
            ->variable($handler($request))->isEqualTo($expectedResult)
            ->mock($requestHandler)->call('__invoke')->withArguments($expectedRequest)->once();
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

    /**
     * testInvoke data provider
     *
     * @return array
     */
    protected function testInvokeDataProvider()
    {
        return [
            [
                'request' => ['headers' => ['host' => ['localhost:9200']]],
                'expectedRequest' => ['headers' => ['host' => ['localhost:9200']], 'url' => 'http://localhost:9200'],
                'headers' => [],
            ],
            [
                'request' => ['headers' => ['host' => ['localhost:9200']]],
                'expectedRequest' => ['headers' => ['host' => ['localhost:9200'], 'X-AddMe' => ['Hello']], 'url' => 'http://localhost:9200'],
                'headers' => ['X-AddMe' => ['Hello']],
            ],
            [
                'request' => ['headers' => ['host' => ['localhost:9200']]],
                'expectedRequest' => ['headers' => ['host' => ['other-host.com']], 'url' => 'http://localhost:9200'],
                'headers' => ['host' => ['other-host.com']],
            ],
            [
                'request' => ['headers' => ['host' => ['localhost:9200']]],
                'expectedRequest' => [
                    'headers' => ['host' => ['localhost:9200'], 'Accept-Encoding' => ['gzip']],
                    'client' => ['decode_content' => true],
                    'url' => 'http://localhost:9200',
                ],
                'headers' => ['Accept-Encoding' => ['gzip']],
            ],
            [
                'request' => ['headers' => ['host' => ['localhost:9200']]],
                'expectedRequest' => [
                    'headers' => ['host' => ['localhost:9200'], 'Accept-Encoding' => ['deflate']],
                    'url' => 'http://localhost:9200',
                ],
                'headers' => ['Accept-Encoding' => ['deflate']],
            ],
        ];
    }
}
