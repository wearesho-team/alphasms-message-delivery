<?php

namespace Wearesho\Delivery\AlphaSms\Tests;

use PHPUnit\Framework\TestCase;
use Wearesho\Delivery\AlphaSms;
use Wearesho\Delivery\MessageInterface;

/**
 * Class MessageCollectionTest
 * @package Wearesho\Delivery\AlphaSms\Tests
 */
class MessageCollectionTest extends TestCase
{
    /** @var AlphaSms\MessageCollection */
    protected $fakeCollection;

    protected function setUp(): void
    {
        $this->fakeCollection = new AlphaSms\MessageCollection();
    }

    public function testType(): void
    {
        $this->assertEquals(MessageInterface::class, $this->fakeCollection->type());
    }
}
