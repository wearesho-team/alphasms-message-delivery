<?php

declare(strict_types=1);

namespace Wearesho\Delivery\AlphaSms;

use Psr\Http\Message\ResponseInterface;
use Wearesho\Delivery;
use GuzzleHttp;

class Service implements Delivery\Batch\ServiceInterface
{
    public const NAME = 'alphasms';

    protected const BASE_URI = 'https://alphasms.ua/api/json.php';

    public function __construct(
        private readonly ConfigInterface $config,
        private readonly GuzzleHttp\ClientInterface $client
    ) {
    }

    public function name(): string
    {
        return static::NAME;
    }

    public function balance(): Delivery\BalanceInterface
    {
        $response = $this->sendRequest([
            ['type' => 'balance',],
        ]);
        $balanceResponse = reset($response);
        if (!array_key_exists('success', $balanceResponse)) {
            throw new Delivery\Exception(
                "Invalid Balance Response body: missing success key",
                4001,
            );
        }
        if (!array_key_exists('data', $balanceResponse) || !is_array($balanceResponse['data'])) {
            throw new Delivery\Exception(
                "Invalid Balance Response body: missing success key",
                4002,
            );
        }
        $balance = $balanceResponse['data'];
        if (!array_key_exists('amount', $balance)) {
            throw new Delivery\Exception(
                "Invalid Balance Response data: missing amount key",
                4011
            );
        }
        if (!array_key_exists('currency', $balance)) {
            throw new Delivery\Exception(
                "Invalid Balance Response data: missing currency key",
                4012
            );
        }
        return new Delivery\Balance(
            (float)$balance['amount'],
            $balance['currency'],
        );
    }

    public function batch(iterable $messages): iterable
    {
        // TODO: Implement batch() method.
    }

    public function send(Delivery\MessageInterface $message): Delivery\ResultInterface
    {
        // TODO: Implement send() method.
    }

    // region Internal API methods
    private function sendRequest(array $data): array
    {
        $body = [
            'auth' => $this->config->getApiKey(),
            'data' => $data,
        ];
        try {
            $response = $this->parseResponse(
                $this->client->request('POST', static::BASE_URI, [
                    GuzzleHttp\RequestOptions::JSON => $body,
                ])
            );
        } catch (GuzzleHttp\Exception\GuzzleException $e) {
            throw new Delivery\Exception(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }

        $this->validateResponse($response);
        return $response['data'];
    }

    private function parseResponse(ResponseInterface $response): array
    {
        try {
            return json_decode(
                $response->getBody()->__toString(),
                true,
                32,
                JSON_THROW_ON_ERROR
            );
        } catch (\JsonException $exception) {
            throw new Delivery\Exception(
                "Invalid JSON in response body",
                $exception->getCode(),
                $exception
            );
        }
    }

    private function validateResponse(array &$responseBody): void
    {
        if (!array_key_exists('success', $responseBody)) {
            throw new Delivery\Exception(
                "Invalid body: missing success key",
                1001
            );
        }
        if ($responseBody['success'] !== true) {
            $errorMessage = $responseBody['error'] ?? "Unknown error, missing error key in response.";
            throw new Delivery\Exception(
                $errorMessage,
                2001
            );
        }
        if (!array_key_exists('data', $responseBody)) {
            throw new Delivery\Exception(
                "Invalid body: missing data key",
                1002
            );
        }
        if (!is_array($responseBody['data'])) {
            throw new Delivery\Exception(
                "Invalid body: data is not an array.",
                3001
            );
        }
        if (count($responseBody['data']) < 1) {
            throw new Delivery\Exception(
                "Invalid body: data is empty.",
                3002
            );
        }
    }
    // endregion
}
