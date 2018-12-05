<?php

namespace Wearesho\Delivery\AlphaSms\Tests\Response;

use Wearesho\Delivery\AlphaSms\Response\Cost;

use PHPUnit\Framework\TestCase;

/**
 * Class CostTest
 * @package Wearesho\Delivery\AlphaSms\Tests\Response
 * @coversDefaultClass Cost
 * @internal
 */
class CostTest extends TestCase
{
    protected const AMOUNT = 1200.3400;
    protected const RECIPIENT = '380000000000';
    protected const CURRENCY = 'UAH';

    /** @var Cost */
    protected $fakeCost;

    protected function setUp(): void
    {
        $this->fakeCost = new Cost(static::RECIPIENT, static::AMOUNT, static::CURRENCY);
    }

    public function testJsonSerialize(): void
    {
        $this->assertArraySubset(
            [
                'recipient' => static::RECIPIENT,
                'amount' => number_format(static::AMOUNT, 2),
                'currency' => static::CURRENCY
            ],
            $this->fakeCost->jsonSerialize()
        );
    }

    public function testGetCurrency(): void
    {
        $this->assertEquals(static::CURRENCY, $this->fakeCost->getCurrency());
    }

    public function testGetAmount(): void
    {
        $this->assertEquals(static::AMOUNT, $this->fakeCost->getAmount());
    }

    public function testGetRecipient(): void
    {
        $this->assertEquals(static::RECIPIENT, $this->fakeCost->getRecipient());
    }

    public function testToString(): void
    {
        $this->assertEquals(
            static::RECIPIENT . ": 1,200.34 " . static::CURRENCY,
            (string)$this->fakeCost
        );
    }
}
