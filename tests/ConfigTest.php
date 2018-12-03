<?php

namespace Wearesho\Delivery\AlphaSms\Tests;

use Wearesho\Delivery;

/**
 * Class ConfigTest
 * @package Wearesho\Delivery\AlphaSms\Tests
 * @coversDefaultClass \Wearesho\Delivery\AlphaSms\Config
 * @internal
 */
class ConfigTest extends ConfigTestCase
{
    protected function setUp(): void
    {
        $this->config = new Delivery\AlphaSms\Config(
            static::LOGIN,
            static::PASSWORD,
            static::SENDER,
            static::KEY
        );
    }
}
