<?php

namespace Http\Adapter\Buzz;

use Buzz\Client\ClientInterface;
use Buzz\Client\FileGetContents;
use Buzz\Exception as BuzzException;
use Buzz\Message\Request as BuzzRequest;
use Buzz\Message\Response as BuzzResponse;
use Http\Client\HttpClient;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Message\MessageFactory;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Http\Client\Exception as HttplugException;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class Client implements HttpClient
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var MessageFactory
     */
    private $messageFactory;

    /**
     * @param FileGetContents|null $client
     * @param MessageFactory|null  $messageFactory
     */
    public function __construct(FileGetContents $client = null, MessageFactory $messageFactory = null)
    {
        $this->client = $client ?: new FileGetContents();
        $this->client->setMaxRedirects(0);

        $this->messageFactory = $messageFactory ?: MessageFactoryDiscovery::find();
    }

    /**
     * {@inheritdoc}
     */
    public function sendRequest(RequestInterface $request)
    {
        $buzzRequest = $this->createRequest($request);

        try {
            $buzzResponse = new BuzzResponse();
            $this->client->send($buzzRequest, $buzzResponse);
        } catch (BuzzException\ClientException $e) {
            throw new HttplugException\TransferException($e->getMessage(), 0, $e);
        }

        return $this->createResponse($buzzResponse);
    }

    /**
     * Converts a PSR request into a BuzzRequest request.
     *
     * @param RequestInterface $request
     *
     * @return BuzzRequest
     */
    private function createRequest(RequestInterface $request)
    {
        $buzzRequest = new BuzzRequest();
        $buzzRequest->setMethod($request->getMethod());
        $buzzRequest->fromUrl($request->getUri()->__toString());
        $buzzRequest->setProtocolVersion($request->getProtocolVersion());
        $buzzRequest->setContent((string) $request->getBody());

        $this->addPsrHeadersToBuzzRequest($request, $buzzRequest);

        return $buzzRequest;
    }

    /**
     * Converts a Buzz response into a PSR response.
     *
     * @param BuzzResponse $response
     *
     * @return ResponseInterface
     */
    private function createResponse(BuzzResponse $response)
    {
        $body = $response->getContent();

        return $this->messageFactory->createResponse(
            $response->getStatusCode(),
            null,
            $this->getBuzzHeaders($response),
            $body,
            number_format($response->getProtocolVersion(), 1)
        );
    }

    /**
     * Apply headers on a Buzz request.
     *
     * @param RequestInterface $request
     * @param BuzzRequest      $buzzRequest
     */
    private function addPsrHeadersToBuzzRequest(RequestInterface $request, BuzzRequest $buzzRequest)
    {
        $headers = $request->getHeaders();
        foreach ($headers as $name => $values) {
            foreach ($values as $header) {
                $buzzRequest->addHeader($name.': '.$header);
            }
        }
    }

    /**
     * Get headers from a Buzz response.
     *
     * @param RequestInterface $request
     * @param BuzzRequest      $buzzRequest
     *
     * @return array
     */
    private function getBuzzHeaders(BuzzResponse $response)
    {
        $buzzHeaders = $response->getHeaders();
        unset($buzzHeaders[0]);
        $headers = [];
        foreach ($buzzHeaders as $headerLine) {
            list($name, $value) = explode(':', $headerLine, 2);
            $headers[$name] = trim($value);
        }

        return $headers;
    }
}
