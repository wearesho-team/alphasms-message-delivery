<?php

namespace Wearesho\Delivery\AlphaSms\Response;

/**
 * Class Cost
 * @package Wearesho\Delivery\AlphaSms\Response
 */
class Cost implements \JsonSerializable
{
    public const WRAPPER = 'prices';
    public const PHONE = 'phone';
    public const PRICE = 'price';
    public const CURRENCY = 'currency';

    /** @var string */
    protected $recipient;

    /** @var float */
    protected $amount;

    /** @var string */
    protected $currency;

    /**
     * Cost constructor.
     *
     * @param string $recipient
     * @param float $amount
     * @param string $currency
     */
    public function __construct(string $recipient, float $amount, string $currency)
    {
        $this->recipient = $recipient;
        $this->amount = $amount;
        $this->currency = $currency;
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }

    public function getRecipient(): string
    {
        return $this->recipient;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }
}
