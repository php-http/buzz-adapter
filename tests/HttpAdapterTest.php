<?php

namespace Http\Adapter\Buzz\Tests;

use Buzz\Browser;
use Http\Adapter\Buzz\Client;
use Http\Client\Tests\HttpClientTest;
use Http\Message\MessageFactory\GuzzleMessageFactory;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
abstract class HttpAdapterTest extends HttpClientTest
{
    /**
     * {@inheritdoc}
     */
    protected function createHttpAdapter()
    {
        $buzz = $this->createBuzzClient();
        $buzz->getClient()->setMaxRedirects(0);

        return new Client(
            $buzz,
            new GuzzleMessageFactory()
        );
    }

    /**
     * Returns a handler for the client.
     *
     * @return Browser
     */
    abstract protected function createBuzzClient();
}
