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
    protected Delivery\AlphaSms\EnvironmentConfig $config;

    protected function setUp(): void
    {
        parent::setUp();
        $this->config = new Delivery\AlphaSms\EnvironmentConfig();
    }

    public function testGetApiKey(): void
    {
        putenv('ALPHASMS_API_KEY=testApiKey');
        $this->assertEquals('testApiKey', $this->config->getApiKey());
        putenv('ALPHASMS_API_KEY');
        $this->expectException(MissingEnvironmentException::class);
        $this->config->getApiKey();
    }

    public function testSender(): void
    {
        putenv('ALPHASMS_SENDER_NAME=wearesho');
        $this->assertEquals('wearesho', $this->config->getSenderName());
        putenv('ALPHASMS_SENDER_NAME');
        $this->expectException(MissingEnvironmentException::class);
        $this->config->getSenderName();
    }

    public function testWebhookUrl(): void
    {
        $testUrl = 'https://wearesho.com/alpha-sms-webhook';
        putenv('ALPHASMS_WEBHOOK_URL=' . $testUrl);
        $this->assertEquals($testUrl, $this->config->getWebhookUrl());
        putenv('ALPHASMS_WEBHOOK_URL');
        $this->assertNull($this->config->getWebhookUrl());
    }
}
