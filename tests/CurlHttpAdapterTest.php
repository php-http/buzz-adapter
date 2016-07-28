<?php

namespace Http\Adapter\Buzz\Tests;

use Buzz\Client\Curl;
use Buzz\Message\RequestInterface as BuzzRequestInterface;
use Http\Client\Exception\RequestException;

class CurlHttpAdapterTest extends HttpAdapterTest
{
    /**
     * {@inheritdoc}
     */
    protected function createBuzzClient()
    {
        $curl = new Curl();
        $curl->setMaxRedirects(0);

        return $curl;
    }

    /**
     * @dataProvider requestProvider
     * @group        integration
     */
    public function testSendRequest($method, $uri, array $headers, $body)
    {
        $validMethods = [
            BuzzRequestInterface::METHOD_POST,
            BuzzRequestInterface::METHOD_PUT,
            BuzzRequestInterface::METHOD_DELETE,
            BuzzRequestInterface::METHOD_PATCH,
            BuzzRequestInterface::METHOD_OPTIONS,
        ];

        if (!in_array($method, $validMethods, true) && $body) {
            $this->setExpectedException(
                RequestException::class,
                sprintf('Buzz\Client\Curl does not support %s requests with a body', $method)
            );
        }

        parent::testSendRequest($method, $uri, $headers, $body);
    }

    /**
     * @dataProvider requestWithOutcomeProvider
     * @group        integration
     */
    public function testSendRequestWithOutcome($uriAndOutcome, $protocolVersion, array $headers, $body)
    {
        if ($body && $protocolVersion === '1.1') {
            $this->setExpectedException(
                RequestException::class,
                'Buzz\Client\Curl does not support GET requests with a body'
            );
        }

        parent::testSendRequestWithOutcome($uriAndOutcome, $protocolVersion, $headers, $body);
    }
}
