<?php

declare(strict_types=1);

namespace Wearesho\Delivery\AlphaSms\Response;

class Balance implements \JsonSerializable
{
    public const TAG = 'balance';
    public const AMOUNT = 'amount';
    public const CURRENCY = 'currency';

    protected float $amount;

    protected string $currency;

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
