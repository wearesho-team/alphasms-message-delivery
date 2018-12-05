<?php

namespace Wearesho\Delivery\AlphaSms\Tests\Response;

use PHPUnit\Framework\TestCase;
use Wearesho\Delivery\AlphaSms\Response;

/**
 * Class CostCollectionTest
 * @package Wearesho\Delivery\AlphaSms\Tests\Response
 */
class CostCollectionTest extends TestCase
{
    protected const AMOUNT = 0.500;
    protected const RECIPIENT = '380000000000';
    protected const CURRENCY = 'UAH';

    /** @var Response\CostCollection */
    protected $fakeCostCollection;

    public function setUp(): void
    {
        $this->fakeCostCollection = new Response\CostCollection([
            new Response\Cost(static::RECIPIENT, static::AMOUNT, static::CURRENCY),
            new Response\Cost(static::RECIPIENT, static::AMOUNT, static::CURRENCY),
        ]);
    }


    public function testSum(): void
    {
        $this->assertEquals(
            static::AMOUNT + static::AMOUNT,
            $this->fakeCostCollection->sum()
        );
    }

    public function testJsonSerialize(): void
    {
        $this->assertEquals(
            [
                new Response\Cost(static::RECIPIENT, static::AMOUNT, static::CURRENCY),
                new Response\Cost(static::RECIPIENT, static::AMOUNT, static::CURRENCY),
            ],
            $this->fakeCostCollection->jsonSerialize()
        );
    }

    public function testToString(): void
    {
        $this->assertEquals(
            "380000000000: 0.50 UAH\n380000000000: 0.50 UAH",
            (string)$this->fakeCostCollection
        );
    }
}
