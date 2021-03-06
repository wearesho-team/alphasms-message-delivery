<?php

namespace Wearesho\Delivery\AlphaSms\Response;

/**
 * Class Balance
 * @package Wearesho\Delivery\AlphaSms\Response
 */
class Balance implements \JsonSerializable
{
    public const TAG = 'balance';
    public const AMOUNT = 'amount';
    public const CURRENCY = 'currency';

    /** @var float */
    protected $amount;

    /** @var string */
    protected $currency;

    /**
     * Balance constructor.
     *
     * @param float $amount
     * @param string $currency
     */
    public function __construct(float $amount, string $currency)
    {
        $this->amount = $amount;
        $this->currency = $currency;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function __toString(): string
    {
        return number_format($this->getAmount(), 2) . " {$this->getCurrency()}";
    }

    public function jsonSerialize(): array
    {
        return [
            'amount' => number_format($this->amount, 2, '.', ''),
            'currency' => $this->currency,
        ];
    }
}
