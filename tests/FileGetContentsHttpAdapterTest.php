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
        $fileGetContents = new FileGetContents();
        $fileGetContents->setMaxRedirects(0);

        return $fileGetContents;
    }
}
