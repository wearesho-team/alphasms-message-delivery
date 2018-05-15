<?php

namespace Wearesho\Delivery\AlphaSms\Tests;

use Horat1us\Environment\MissingEnvironmentException;
use PHPUnit\Framework\TestCase;
use Wearesho\Delivery;

/**
 * Class EnvironmentConfigTest
 * @package Wearesho\Delivery\AlphaSms\Tests
 * @coversDefaultClass \Wearesho\Delivery\AlphaSms\
 */
class EnvironmentConfigTest extends TestCase
{
    /** @var Delivery\AlphaSms\EnvironmentConfig */
    protected $config;

    protected function setUp(): void
    {
        parent::setUp();
        $this->config = new Delivery\AlphaSms\EnvironmentConfig;
    }

    public function testGetLogin(): void
    {
        putenv('ALPHASMS_LOGIN');
        $this->assertNull($this->config->getLogin());
        putenv('ALPHASMS_LOGIN=testLogin');
        $this->assertEquals('testLogin', $this->config->getLogin());
    }

    public function testGetPassword(): void
    {
        putenv('ALPHASMS_PASSWORD');
        $this->assertNull($this->config->getPassword());
        putenv('ALPHASMS_PASSWORD=Qwerty123');
        $this->assertEquals('Qwerty123', $this->config->getPassword());
    }

    public function testGetApiKey(): void
    {
        putenv('ALPHASMS_API_KEY');
        $this->assertNull($this->config->getApiKey());
        putenv('ALPHASMS_API_KEY=2l3nrihx2xr23zdsSDZ');
        $this->assertEquals('2l3nrihx2xr23zdsSDZ', $this->config->getApiKey());
    }

    public function testSender(): void
    {
        putenv('ALPHASMS_SENDER_NAME=wearesho');
        $this->assertEquals('wearesho', $this->config->getSenderName());
        putenv('ALPHASMS_SENDER_NAME');
        $this->expectException(MissingEnvironmentException::class);
        $this->config->getSenderName();
    }
}
