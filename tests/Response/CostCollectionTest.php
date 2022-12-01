<?php

declare(strict_types=1);

namespace Wearesho\Delivery\AlphaSms\Tests\Response;

use PHPUnit\Framework\TestCase;
use Wearesho\Delivery\AlphaSms\Response;

class CostCollectionTest extends TestCase
{
    protected const AMOUNT = 0.500;
    protected const RECIPIENT = 'recipient';
    protected const CURRENCY = 'currency';

    protected Response\CostCollection $fakeCostCollection;

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
