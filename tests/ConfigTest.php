<?php

declare(strict_types=1);

namespace Wearesho\Delivery\AlphaSms\Tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Wearesho\Delivery\AlphaSms\Config;

class ConfigTest extends TestCase
{
    /**
     * @return array<string, array{string, string, string|null}>
     */
    public static function configDataProvider(): array
    {
        return [
            'basic configuration' => [
                'api-key-123',
                'TestSender',
                'https://wearesho.com/alpha-sms-webhook'
            ],
            'empty values' => [
                '',
                '',
                null,
            ],
            'special characters' => [
                'key@123#$%',
                'Sender Name!',
                null,
            ],
        ];
    }

    #[DataProvider('configDataProvider')]
    public function testConfigGetters(string $apiKey, string $senderName, ?string $webhook): void
    {
        $config = new Config($apiKey, $senderName, $webhook);

        $this->assertSame($apiKey, $config->getApiKey());
        $this->assertSame($senderName, $config->getSenderName());
        $this->assertEquals($webhook, $config->getWebhookUrl());
    }

    public function testConfigImmutability(): void
    {
        $config = new Config('test-key', 'test-sender');

        $reflection = new \ReflectionClass($config);
        $apiKeyProperty = $reflection->getProperty('apiKey');
        $senderNameProperty = $reflection->getProperty('senderName');

        $this->assertTrue($apiKeyProperty->isReadOnly());
        $this->assertTrue($senderNameProperty->isReadOnly());
    }
}
