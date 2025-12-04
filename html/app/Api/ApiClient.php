<?php

namespace App\Api;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class ApiClient
{
    public const DEFAULT_REQUEST_ATTEMPTS_COUNT = 3;
    public const DEFAULT_SLEEP_SECONDS = 1;

    /**
     * @var Client
     */
    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    protected function get(
        string $uri,
        array $options = [],
        int $retryCount = self::DEFAULT_REQUEST_ATTEMPTS_COUNT,
        int $sleepOnRetrySeconds = self::DEFAULT_SLEEP_SECONDS
    ): ResponseInterface {
        return $this->sendRequest(
            function () use ($uri, $options) {
                return $this->client->get($uri, $options);
            },
            $retryCount,
            $sleepOnRetrySeconds
        );
    }

    protected function post(
        string $uri,
        array $options = [],
        int $retryCount = self::DEFAULT_REQUEST_ATTEMPTS_COUNT,
        int $sleepOnRetrySeconds = self::DEFAULT_SLEEP_SECONDS
    ): ResponseInterface {
        return $this->sendRequest(
            function () use ($uri, $options) {
                return $this->client->post($uri, $options);
            },
            $retryCount,
            $sleepOnRetrySeconds
        );
    }

    protected function delete(
        string $uri,
        array $options = [],
        int $retryCount = self::DEFAULT_REQUEST_ATTEMPTS_COUNT,
        int $sleepOnRetrySeconds = self::DEFAULT_SLEEP_SECONDS
    ): ResponseInterface {
        return $this->sendRequest(
            function () use ($uri, $options) {
                return $this->client->delete($uri, $options);
            },
            $retryCount,
            $sleepOnRetrySeconds
        );
    }

    protected function patch(
        string $uri,
        array $options = [],
        int $retryCount = self::DEFAULT_REQUEST_ATTEMPTS_COUNT,
        int $sleepOnRetrySeconds = self::DEFAULT_SLEEP_SECONDS
    ): ResponseInterface {
        return $this->sendRequest(
            function () use ($uri, $options) {
                return $this->client->patch($uri, $options);
            },
            $retryCount,
            $sleepOnRetrySeconds
        );
    }

    private function sendRequest(
        callable $callback,
        int $maxRetryCount = self::DEFAULT_REQUEST_ATTEMPTS_COUNT,
        int $sleepOnRetrySeconds = self::DEFAULT_SLEEP_SECONDS
    ): ResponseInterface {
        $attempt = 0;
        $needRetry = true;

        do {
            $attempt++;
            try {
                /** @var ResponseInterface $response */
                $response = $callback();
                $needRetry = false;
            } catch (GuzzleException $exception) {
                if (
                    $attempt >= $maxRetryCount
                    || !in_array(
                        $exception->getCode(),
                        [Response::HTTP_UNPROCESSABLE_ENTITY, Response::HTTP_FORBIDDEN, Response::HTTP_NOT_FOUND],
                        true
                    )
                ) {
                    throw $exception;
                }

                usleep($sleepOnRetrySeconds * 1000);
            }
        } while ($needRetry);

        return $response;
    }
}
