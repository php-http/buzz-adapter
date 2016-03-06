<?php

namespace Http\Adapter\Buzz\Tests;

use Buzz\Client\FileGetContents;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class FileGetContentsHttpAdapterTest extends HttpAdapterTest
{
    /**
     * {@inheritdoc}
     */
    protected function createBuzzClient()
    {
        return new FileGetContents();
    }
}
