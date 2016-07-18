<?php

namespace Http\Adapter\Buzz\Tests;

use Buzz\Browser;

class BrowserHttpAdapterTest extends HttpAdapterTest
{
    /**
     * {@inheritdoc}
     */
    protected function createBuzzClient()
    {
        $browser = new Browser();
        $browser->getClient()->setMaxRedirects(0);

        return $browser;
    }
}
