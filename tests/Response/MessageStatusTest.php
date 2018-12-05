<?php

namespace Wearesho\Delivery\AlphaSms\Tests\Response;

use PHPUnit\Framework\TestCase;
use Wearesho\Delivery\AlphaSms\Response\MessageStatus;

/**
 * Class MessageStatusTest
 * @package Wearesho\Delivery\AlphaSms\Tests\Response
 * @coversDefaultClass MessageStatus
 * @internal
 */
class MessageStatusTest extends TestCase
{
    protected const GATEWAY_ID = 'gateway_id';
    protected const VALUE = 200;
    protected const SMS_COUNT = 10;
    protected const ID = 'id';

    /** @var MessageStatus */
    protected $fakeMessageStatus;

    protected function setUp(): void
    {
        $this->fakeMessageStatus = new MessageStatus(
            static::GATEWAY_ID,
            static::VALUE,
            static::SMS_COUNT,
            static::ID
        );
    }

    public function testGetSmsCount(): void
    {
        $this->assertEquals(
            static::SMS_COUNT,
            $this->fakeMessageStatus->getSmsCount()
        );
    }

    public function testGetId(): void
    {
        $this->assertEquals(
            static::ID,
            $this->fakeMessageStatus->getId()
        );
    }

    public function testGetGatewayId(): void
    {
        $this->assertEquals(
            static::GATEWAY_ID,
            $this->fakeMessageStatus->getGatewayId()
        );
    }

    public function testGetValue(): void
    {
        $this->assertEquals(
            static::VALUE,
            $this->fakeMessageStatus->getValue()
        );
    }

    public function testIsSuccess(): void
    {
        $this->assertFalse($this->fakeMessageStatus->isSuccess());
    }
}
