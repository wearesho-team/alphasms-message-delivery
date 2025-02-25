<?php

declare(strict_types=1);

namespace Wearesho\Delivery\AlphaSms\VoiceOtp;

class Request implements \JsonSerializable
{
    public const TYPE = 'call/otp';

    public function __construct(
        private readonly int $id,
        private readonly string $phoneNumber,
    ) {
    }

    public function id(): int
    {
        return $this->id;
    }

    public function phoneNumber(): string
    {
        return $this->phoneNumber;
    }

    final public function jsonSerialize(): array
    {
        return [
            'type' => self::TYPE,
            'id' => $this->id(),
            'phone' => (int)$this->phoneNumber(),
        ];
    }
}
