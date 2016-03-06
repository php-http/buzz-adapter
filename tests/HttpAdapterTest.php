<?php

namespace Http\Adapter\Buzz\Tests;

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
        return new Client(
            $this->createBuzzClient(),
            new GuzzleMessageFactory()
        );
    }

    /**
     * Returns a handler for the client.
     *
     * @return object
     */
    abstract protected function createBuzzClient();
}
