<?php

namespace Wearesho\Delivery\AlphaSms\Response;

/**
 * Class MessageStatus
 * @package Wearesho\Delivery\AlphaSms\Response
 */
class MessageStatus
{
    public const SUCCESS = 1;

    /** @var string */
    protected $gatewayId;

    /** @var int */
    protected $value;

    /** @var int */
    protected $smsCount;

    /** @var string|null */
    protected $id;

    public function __construct(
        string $gatewayId,
        int $value,
        int $smsCount,
        string $id = null
    ) {
        $this->gatewayId = $gatewayId;
        $this->value = $value;
        $this->smsCount = $smsCount;
        $this->id = $id;
    }

    public function getGatewayId(): string
    {
        return $this->gatewayId;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function getSmsCount(): int
    {
        return $this->smsCount;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function isSuccess(): bool
    {
        return $this->value === static::SUCCESS;
    }
}
