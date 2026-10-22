<?php

declare(strict_types=1);

namespace Wearesho\Delivery\AlphaSms\TelegramOtp;

class Request implements \JsonSerializable
{
    public const TYPE = 'telegram';

    public const MIN_CODE_LENGTH = 4;
    public const MAX_CODE_LENGTH = 8;
    public const MIN_LIFETIME = 60;

    /**
     * @param int $id Unique message identifier in client system
     * @param string $phoneNumber Recipient phone number in international format
     * @param string $code One-time password, from 4 to 8 digits
     * @param int|null $lifetime Telegram message lifetime in seconds (min 60, AlphaSMS default is 2 days)
     * @param string|null $webhookUrl URL for delivery status webhooks
     */
    public function __construct(
        private readonly int $id,
        private readonly string $phoneNumber,
        private readonly string $code,
        private readonly ?int $lifetime = null,
        private readonly ?string $webhookUrl = null,
    ) {
        $codeLength = strlen($code);
        if (
            $codeLength < self::MIN_CODE_LENGTH
            || $codeLength > self::MAX_CODE_LENGTH
            || !ctype_digit($code)
        ) {
            throw new \InvalidArgumentException(
                "TelegramOTP request 'code' must contain from "
                . self::MIN_CODE_LENGTH . " to " . self::MAX_CODE_LENGTH . " digits."
            );
        }
        if (!is_null($lifetime) && $lifetime < self::MIN_LIFETIME) {
            throw new \InvalidArgumentException(
                "TelegramOTP request 'lifetime' must be at least " . self::MIN_LIFETIME . " seconds."
            );
        }
    }

    public function id(): int
    {
        return $this->id;
    }

    public function phoneNumber(): string
    {
        return $this->phoneNumber;
    }

    public function code(): string
    {
        return $this->code;
    }

    public function lifetime(): ?int
    {
        return $this->lifetime;
    }

    public function webhookUrl(): ?string
    {
        return $this->webhookUrl;
    }

    final public function jsonSerialize(): array
    {
        $body = [
            'type' => self::TYPE,
            'id' => $this->id(),
            'phone' => (int)$this->phoneNumber(),
            'message' => $this->code(),
        ];
        if (!is_null($this->lifetime())) {
            $body['telegram_lifetime'] = $this->lifetime();
        }
        if (!is_null($this->webhookUrl())) {
            $body['hook'] = $this->webhookUrl();
        }
        return $body;
    }
}
