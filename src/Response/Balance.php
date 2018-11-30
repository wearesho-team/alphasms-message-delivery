<?php

namespace Wearesho\Delivery\AlphaSms\Response;

/**
 * Class Balance
 * @package Wearesho\Delivery\AlphaSms\Response
 */
class Balance
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
}
