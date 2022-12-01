<?php

declare(strict_types=1);

namespace Wearesho\Delivery\AlphaSms\Tests;

use PHPUnit\Framework\TestCase;
use Wearesho\Delivery;

class ConfigTest extends TestCase
{
    protected Delivery\AlphaSms\Config $config;

    protected function setUp(): void
    {
        parent::setUp();
        $this->config = new Delivery\AlphaSms\Config();
    }

    public function testGetLogin(): void
    {
        $this->config->login = 'Login';
        $this->assertEquals(
            'Login',
            $this->config->getLogin()
        );
    }

    public function testGetPassword(): void
    {
        $this->config->password = 'Password';
        $this->assertEquals(
            'Password',
            $this->config->getPassword()
        );
    }

    public function testGetApiKey(): void
    {
        $this->config->apiKey = 'ApiKey';
        $this->assertEquals(
            'ApiKey',
            $this->config->getApiKey()
        );
    }
}
