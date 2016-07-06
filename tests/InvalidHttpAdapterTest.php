<?php

namespace Http\Adapter\Buzz\Tests;

use Http\Adapter\Buzz\Client;
use Http\Message\MessageFactory\GuzzleMessageFactory;

class InvalidHttpAdapterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The client passed to the Buzz adapter must either implement Buzz\Client\ClientInterface
     * or be an instance of Buzz\Browser. You passed stdClass.
     */
    public function testConstructorThrowsExceptionWhenInvalidClientInstanceIsUsed()
    {
        return new Client(
            new \stdClass(),
            new GuzzleMessageFactory()
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The client passed to the Buzz adapter must either implement Buzz\Client\ClientInterface
     * or be an instance of Buzz\Browser. You passed string.
     */
    public function testConstructorThrowsExceptionWhenNonObjectClientIsUsed()
    {
        return new Client(
            'This should be an object!',
            new GuzzleMessageFactory()
        );
    }
}
