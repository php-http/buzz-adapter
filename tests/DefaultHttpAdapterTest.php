<?php

namespace Http\Adapter\Buzz\Tests;

class DefaultHttpAdapterTest extends HttpAdapterTest
{
    /**
     * {@inheritdoc}
     */
    protected function createBuzzClient()
    {
        // returning null here should cause the adapter to create a default client for us
        return;
    }
}
