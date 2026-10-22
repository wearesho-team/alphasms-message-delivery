<?php

declare(strict_types=1);

namespace Wearesho\Delivery\AlphaSms\TelegramOtp;

use Wearesho\Delivery;

class Response implements \JsonSerializable
{
    private function __construct(
        private readonly string $messageId
    ) {
    }

    /**
     * Note: API returns only msg_id in response data, despite documentation also mentions id, data and parts.
     */
    public static function parse(array $responseData): self
    {
        if (!array_key_exists('msg_id', $responseData)) {
            throw new Delivery\Exception("Missing required 'msg_id' field in TelegramOTP response.");
        }

        $messageId = $responseData['msg_id'];
        if (!is_int($messageId) && !(is_string($messageId) && ctype_digit($messageId))) {
            throw new Delivery\Exception("TelegramOTP response 'msg_id' must be an integer.");
        }

        return new self((string)$messageId);
    }

    /**
     * Message identifier in AlphaSMS, used in delivery status webhooks.
     */
    public function messageId(): string
    {
        return $this->messageId;
    }

    public function jsonSerialize(): array
    {
        return [
            'messageId' => $this->messageId,
        ];
    }
}
