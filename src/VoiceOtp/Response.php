<?php

declare(strict_types=1);

namespace Wearesho\Delivery\AlphaSms\VoiceOtp;

use Wearesho\Delivery;

class Response implements \JsonSerializable
{
    private function __construct(
        private readonly string $code,
        private readonly float $price
    ) {
    }

    public static function parse(array $responseData): self
    {
        if (!array_key_exists("code", $responseData)) {
            throw new Delivery\Exception("Missing required 'code' field in VoiceOTP response.");
        }

        if (!is_string($responseData['code'])) {
            throw new Delivery\Exception("VoiceOTP response 'code' must be a string.");
        }

        if (mb_strlen($responseData['code']) !== 4 || !ctype_digit($responseData['code'])) {
            throw new Delivery\Exception("VoiceOTP response 'code' must be exactly 4 digits.");
        }

        if (!array_key_exists("price", $responseData)) {
            throw new Delivery\Exception("Missing required 'price' field in VoiceOTP response.");
        }

        if (!is_numeric($responseData['price'])) {
            throw new Delivery\Exception("VoiceOTP response 'price' must be a numeric value.");
        }

        $price = (float)$responseData['price'];
        if ($price < 0) {
            throw new Delivery\Exception("VoiceOTP response 'price' cannot be negative.");
        }

        return new self($responseData['code'], $price);
    }

    public function code(): string
    {
        return $this->code;
    }

    public function price(): float
    {
        return $this->price;
    }

    public function jsonSerialize(): array
    {
        return [
            'code' => $this->code,
            'price' => $this->price,
        ];
    }
}
