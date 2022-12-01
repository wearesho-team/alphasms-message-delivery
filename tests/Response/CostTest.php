<?php

declare(strict_types=1);

namespace Wearesho\Delivery\AlphaSms\Tests\Response;

use Wearesho\Delivery\AlphaSms\Response\Cost;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass Cost
 */
class CostTest extends TestCase
{
    protected const AMOUNT = 0.3400;
    protected const RECIPIENT = 'recipient';
    protected const CURRENCY = 'currency';

    protected Cost $fakeCost;

    protected function setUp(): void
    {
        $this->fakeCost = new Cost(static::RECIPIENT, static::AMOUNT, static::CURRENCY);
    }

    public function testJsonSerialize(): void
    {
        $this->assertEquals(
            [
                'recipient' => static::RECIPIENT,
                'amount' => static::AMOUNT,
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
}
