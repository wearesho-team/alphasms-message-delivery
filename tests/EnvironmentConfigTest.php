<?php

namespace Wearesho\Delivery\AlphaSms\Tests;

use Wearesho\Delivery;

/**
 * Class EnvironmentConfigTest
 * @package Wearesho\Delivery\AlphaSms\Tests
 * @coversDefaultClass \Wearesho\Delivery\AlphaSms\
 */
class EnvironmentConfigTest extends ConfigTestCase
{
    /** @var Delivery\AlphaSms\EnvironmentConfig */
    protected $config;

    protected function setUp(): void
    {
        putenv('ALPHASMS_LOGIN=' . static::LOGIN);
        putenv('ALPHASMS_PASSWORD=' . static::PASSWORD);
        putenv('ALPHASMS_SENDER_NAME=' . static::SENDER);
        putenv('ALPHASMS_KEY=' . static::KEY);

        $this->config = new Delivery\AlphaSms\EnvironmentConfig();
    }
}
