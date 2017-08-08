<?php

namespace Http\Adapter\Buzz;

use Buzz\Browser;
use Buzz\Client\ClientInterface;
use Buzz\Client\Curl;
use Buzz\Client\FileGetContents;
use Buzz\Exception as BuzzException;
use Buzz\Message\Request as BuzzRequest;
use Buzz\Message\RequestInterface as BuzzRequestInterface;
use Buzz\Message\Response as BuzzResponse;
use Http\Client\HttpClient;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Message\ResponseFactory;
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
     * @var ResponseFactory
     */
    private $responseFactory;

    /**
     * @param ClientInterface|Browser|null $client
     * @param ResponseFactory|null         $responseFactory
     */
    public function __construct($client = null, ResponseFactory $responseFactory = null)
    {
        $this->client = $client;

        if ($this->client === null) {
            $this->client = new FileGetContents();
            $this->client->setMaxRedirects(0);
        }

        if ((!$this->client instanceof ClientInterface) && (!$this->client instanceof Browser)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The client passed to the Buzz adapter must either implement %s or be an instance of %s. You passed %s.',
                    ClientInterface::class,
                    Browser::class,
                    is_object($client) ? get_class($client) : gettype($client)
                )
            );
        }

        $this->responseFactory = $responseFactory ?: MessageFactoryDiscovery::find();
    }

    /**
     * {@inheritdoc}
     */
    public function sendRequest(RequestInterface $request)
    {
        $this->assertRequestHasValidBody($request);

        $buzzRequest = $this->createRequest($request);

        try {
            $buzzResponse = new BuzzResponse();
            $this->client->send($buzzRequest, $buzzResponse);
        } catch (BuzzException\RequestException $e) {
            if (28 === $e->getCode() || strstr($e->getMessage(), 'failed to open stream: Operation timed out')) {
                // Timeout
                throw new HttplugException\NetworkException($e->getMessage(), $request, $e);
            }
            throw new HttplugException\RequestException($e->getMessage(), $request, $e);
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

        return $this->responseFactory->createResponse(
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
     * @param BuzzResponse $response
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

    /**
     * Assert that the request has a valid body based on the request method.
     *
     * @param RequestInterface $request
     */
    private function assertRequestHasValidBody(RequestInterface $request)
    {
        $validMethods = [
            BuzzRequestInterface::METHOD_POST,
            BuzzRequestInterface::METHOD_PUT,
            BuzzRequestInterface::METHOD_DELETE,
            BuzzRequestInterface::METHOD_PATCH,
            BuzzRequestInterface::METHOD_OPTIONS,
        ];

        // The Buzz Curl client does not send request bodies for request methods such as GET, HEAD and TRACE. Instead of
        // silently ignoring the request body in these cases, throw an exception to make users aware.
        if ($this->client instanceof Curl &&
            $request->getBody()->getSize() &&
            !in_array(strtoupper($request->getMethod()), $validMethods, true)
        ) {
            throw new HttplugException\RequestException(
                sprintf('%s does not support %s requests with a body', Curl::class, $request->getMethod()),
                $request
            );
        }
    }
}
