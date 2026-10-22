<?php

declare(strict_types=1);

namespace Wearesho\Delivery\AlphaSms\TelegramOtp;

use Wearesho\Delivery;

class Response implements \JsonSerializable
{
    private function __construct(
        private readonly int $id,
        private readonly string $messageId
    ) {
    }

    public static function parse(array $responseData): self
    {
        if (!array_key_exists('id', $responseData)) {
            throw new Delivery\Exception("Missing required 'id' field in TelegramOTP response.");
        }

        if (!self::isInteger($responseData['id'])) {
            throw new Delivery\Exception("TelegramOTP response 'id' must be an integer.");
        }

        if (!array_key_exists('msg_id', $responseData)) {
            throw new Delivery\Exception("Missing required 'msg_id' field in TelegramOTP response.");
        }

        if (!self::isInteger($responseData['msg_id'])) {
            throw new Delivery\Exception("TelegramOTP response 'msg_id' must be an integer.");
        }

        return new self((int)$responseData['id'], (string)$responseData['msg_id']);
    }

    /**
     * Message identifier in client system (same as in request).
     */
    public function id(): int
    {
        return $this->id;
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
            'id' => $this->id,
            'messageId' => $this->messageId,
        ];
    }

    private static function isInteger(mixed $value): bool
    {
        return is_int($value) || (is_string($value) && ctype_digit($value));
    }
}
