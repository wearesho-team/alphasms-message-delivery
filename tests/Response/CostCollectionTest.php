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
    protected const RECIPIENT = 'recipient';
    protected const CURRENCY = 'currency';

    /** @var Response\CostCollection */
    protected $fakeCostCollection;

    public function setUp(): void
    {
        $this->fakeCostCollection = new Response\CostCollection();
    }


    public function testSum(): void
    {
        $this->fakeCostCollection
            ->append(new Response\Cost(static::RECIPIENT, static::AMOUNT, static::CURRENCY))
            ->append(new Response\Cost(static::RECIPIENT, static::AMOUNT, static::CURRENCY));

        $this->assertEquals(
            static::AMOUNT + static::AMOUNT,
            $this->fakeCostCollection->sum()
        );
    }
}
