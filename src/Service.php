<?php

declare(strict_types=1);

namespace Wearesho\Delivery\AlphaSms;

use Carbon\Carbon;
use Psr\Http\Message\ResponseInterface;
use Wearesho\Delivery;
use GuzzleHttp;

class Service implements Delivery\Batch\ServiceInterface
{
    public const NAME = 'alphasms';

    public const CHANNEL_SMS = 'sms';
    public const CHANNEL_VIBER = 'viber';

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

    /**
     * @throws Delivery\Exception
     */
    public function voiceOtp(VoiceOtp\Request $request): VoiceOtp\Response
    {
        $response = $this->sendRequest([
            $request,
        ]);
        return VoiceOtp\Response::parse(reset($response)['data']);
    }

    public function send(Delivery\MessageInterface $message): Delivery\ResultInterface
    {
        [$result] = [...$this->batch([$message])];
        return $result;
    }

    /**
     * @param iterable $messages
     * @return iterable<Delivery\MessageInterface>
     * @throws Delivery\Exception
     */
    public function batch(iterable $messages): iterable
    {
        $messagesArray = [];
        $requests = [];
        foreach ($messages as $message) {
            $key = "i_" . uniqid('', true);
            $messagesArray[$key] = $message;
            $requests[] = $this->getRequestBody($key, $message);
        }

        $response = $this->sendRequest($requests);
        if (count($response) !== count($messagesArray)) {
            throw new Delivery\Exception(
                "Invalid response messages count, "
                . count($response) . " given, "
                . count($messagesArray) . " expected.",
                7001
            );
        }

        foreach ($response as $responseItem) {
            yield $this->parseResponseItem($responseItem, $messagesArray);
        }
    }

    // region Internal API methods
    protected function getRequestBody(string $id, Delivery\MessageInterface $message): array
    {
        $senderName = Delivery\Options::get($message, Delivery\Options::SENDER_NAME)
            ?? $this->config->getSenderName();
        $messageChannel = Delivery\Options::get($message, Delivery\Options::CHANNEL)
            ?? self::CHANNEL_SMS;
        $this->validateChannelName($messageChannel);

        $request = [
            'id' => $id,
            'type' => $messageChannel,
            'phone' => $message->getRecipient(),
        ];
        $webhookUrl = Delivery\Options::get($message, Options::WEBHOOK_URL)
            ?? $this->config->getWebhookUrl();
        if (!is_null($webhookUrl)) {
            $request['hook'] = $webhookUrl;
        }

        $channelDependentOptions = [
            'signature' => $senderName,
            'message' => $message->getText(),
        ];
        if (!is_null($ttl = Delivery\Options::get($message, Delivery\Options::TTL))) {
            $channelDependentOptions['lifetime'] = $ttl;
        }

        foreach ($channelDependentOptions as $option => $value) {
            $request[$messageChannel . '_' . $option] = $value;
        }
        if ($messageChannel === self::CHANNEL_VIBER) {
            $request['viber_type'] = 'text';
        }
        return $request;
    }

    private function parseResponseItem(array $responseItem, array &$messagesArray): Delivery\ResultInterface
    {
        if (!array_key_exists('success', $responseItem)) {
            throw new Delivery\Exception(
                "Missing success key in responseItem",
                8001
            );
        }
        if (!array_key_exists('data', $responseItem)) {
            throw new Delivery\Exception(
                "Missing data key in responseItem",
                8002
            );
        }
        if (!array_key_exists('id', $responseItem['data'])) {
            throw new Delivery\Exception(
                "Missing id key in responseItem data",
                8003
            );
        }
        $message = $messagesArray[$responseItem['data']['id']];
        if (is_null($message)) {
            throw new Delivery\Exception(
                "Invalid id in responseItem data",
                8004
            );
        }
        return ($responseItem['success'] !== true)
            ? $this->parseResponseItemFailure($responseItem, $message)
            : $this->parseResponseItemSuccess($responseItem, $message);
    }

    private function parseResponseItemFailure(
        array $responseItem,
        Delivery\MessageInterface $message
    ): Delivery\ResultInterface {
        if (!array_key_exists('error', $responseItem)) {
            throw new Delivery\Exception(
                "Missing error key in responseItem",
                8011
            );
        }

        return new Delivery\Result(
            messageId: $responseItem['data']['id'],
            message: $message,
            status: Delivery\Result\Status::Rejected,
            reason: $responseItem['error']
        );
    }

    private function parseResponseItemSuccess(
        array $responseItem,
        Delivery\MessageInterface $message
    ): Delivery\ResultInterface {
        if (!array_key_exists('msg_id', $responseItem['data'])) {
            throw new Delivery\Exception(
                "Missing msg_id key in responseItem data",
                8101
            );
        }
        return new Delivery\Result(
            messageId: (string)$responseItem['data']['msg_id'],
            message: $message,
            status: Delivery\Result\Status::Accepted,
        );
    }

    private function validateChannelName(string $channel): void
    {
        if (!in_array($channel, [self::CHANNEL_VIBER, self::CHANNEL_SMS])) {
            throw new Delivery\Exception("Unsupported Channel: $channel", 10001);
        }
    }

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
