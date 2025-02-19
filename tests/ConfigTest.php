<?php

declare(strict_types=1);

namespace Wearesho\Delivery\AlphaSms\Tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Wearesho\Delivery\AlphaSms\Config;

class ConfigTest extends TestCase
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function configDataProvider(): array
    {
        return [
            'basic configuration' => [
                'api-key-123',
                'TestSender',
            ],
            'empty values' => [
                '',
                '',
            ],
            'special characters' => [
                'key@123#$%',
                'Sender Name!',
            ],
        ];
    }

    #[DataProvider('configDataProvider')]
    public function testConfigGetters(string $apiKey, string $senderName): void
    {
        $config = new Config($apiKey, $senderName);

        $this->assertSame($apiKey, $config->getApiKey());
        $this->assertSame($senderName, $config->getSenderName());
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
