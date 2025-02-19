<?php

declare(strict_types=1);

namespace Wearesho\Delivery\AlphaSms\Tests;

use GuzzleHttp;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Wearesho\Delivery;

class ServiceTest extends TestCase
{
    private const TEST_SENDER_NAME = 'testSenderName';
    private const TEST_API_KEY = '96ac8bf5-4347-4c48-aa8e-1147aecc3bd2'; // Random UUID

    private GuzzleHttp\ClientInterface&MockObject $client;
    private Delivery\AlphaSms\Service $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = $this->createMock(GuzzleHttp\ClientInterface::class);
        $this->service = new Delivery\AlphaSms\Service(
            new Delivery\AlphaSms\Config(
                apiKey: self::TEST_API_KEY,
                senderName: self::TEST_SENDER_NAME
            ),
            $this->client
        );
    }

    public static function balanceDataProvider(): array
    {
        return [
            [
                [
                    'auth' => self::TEST_API_KEY,
                    'data' => [
                        [
                            'type' => 'balance',
                        ]
                    ],
                ],
                <<<JSON
{
    "success": true,
    "data": [
        {
            "success": true,
            "data": {
                "amount": 31.1683,
                "currency": "UAH"
            }
        }
    ]
}
JSON,
                new Delivery\Balance(31.1683, "UAH"),
            ],
            [
                [
                    'auth' => self::TEST_API_KEY,
                    'data' => [
                        [
                            'type' => 'balance',
                        ]
                    ],
                ],
                <<<JSON
{
    "success": false,
    "error": "Access denied"
}
JSON,
                null,
                new Delivery\Exception("Access denied", 2001),
            ],
        ];
    }

    #[DataProvider('balanceDataProvider')]
    public function testBalance(
        array $expectedRequestBody,
        string $mockResponseBody,
        ?Delivery\BalanceInterface $expectedBalance = null,
        ?Delivery\Exception $expectedException = null
    ): void {
        $this->client
            ->expects($this->once())
            ->method('request')
            ->with('POST', 'https://alphasms.ua/api/json.php', [
                GuzzleHttp\RequestOptions::JSON => $expectedRequestBody,
            ])
            ->willReturn(new GuzzleHttp\Psr7\Response(
                200,
                [],
                $mockResponseBody
            ));

        if (!is_null($expectedException)) {
            $this->expectExceptionObject($expectedException);
        }

        $balance = $this->service->balance();
        $this->assertEquals($expectedBalance, $balance);
    }
}
