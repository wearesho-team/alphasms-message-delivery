<?php

namespace Wearesho\Delivery\AlphaSms\Tests;

use PHPUnit\Framework\TestCase;
use Wearesho\Delivery\AlphaSms\ConfigInterface;

/**
 * Class ConfigTestCase
 * @package Wearesho\Delivery\AlphaSms\Tests
 */
class ConfigTestCase extends TestCase
{
    protected const LOGIN = 'login';
    protected const PASSWORD = 'password';
    protected const SENDER = 'sender';
    protected const KEY = 'key';

    /** @var ConfigInterface */
    protected $config;

    public function testGetLogin(): void
    {
        $this->assertEquals(static::LOGIN, $this->config->getLogin());
    }

    public function testGetPassword(): void
    {
        $this->assertEquals(static::PASSWORD, $this->config->getPassword());
    }

    public function testGetSenderName(): void
    {
        $this->assertEquals(static::SENDER, $this->config->getSenderName());
    }

    public function testGetKey(): void
    {
        $this->assertEquals(static::KEY, $this->config->getKey());
    }
}
