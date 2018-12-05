<?php

namespace Wearesho\Delivery\AlphaSms\Tests\Response;

use PHPUnit\Framework\TestCase;
use Wearesho\Delivery\AlphaSms;

/**
 * Class MessageStatusCollectionTest
 * @package Wearesho\Delivery\AlphaSms\Tests\Response
 */
class MessageStatusCollectionTest extends TestCase
{
    protected const GATEWAY_ID = 'gateway_id';
    protected const VALUE = 200;
    protected const SMS_COUNT = 2;

    /** @var AlphaSms\Response\MessageStatusCollection */
    protected $fakeCollection;

    protected function setUp(): void
    {
        $this->fakeCollection = new AlphaSms\Response\MessageStatusCollection();
    }

    public function testType(): void
    {
        $this->assertEquals(AlphaSms\Response\MessageStatus::class, $this->fakeCollection->type());
    }

    public function testTotalSmsCount(): void
    {
        $messageStatus = new AlphaSms\Response\MessageStatus(
            static::GATEWAY_ID,
            static::VALUE,
            static::SMS_COUNT
        );

        $this->fakeCollection
            ->append($messageStatus)
            ->append($messageStatus)
            ->append($messageStatus);

        $this->assertEquals(6, $this->fakeCollection->totalSmsCount());
    }
}
