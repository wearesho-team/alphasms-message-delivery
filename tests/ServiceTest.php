<?php

declare(strict_types=1);

namespace Wearesho\Delivery\AlphaSms\Tests;

use Carbon\Carbon;
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
    private Delivery\AlphaSms\ConfigInterface&MockObject $config;
    private Delivery\AlphaSms\Service $service;

    protected function setUp(): void
    {
        $this->client = $this->createMock(GuzzleHttp\ClientInterface::class);
        $this->config = $this->createMock(Delivery\AlphaSms\ConfigInterface::class);
        $this->config->method('getApiKey')->willReturn(self::TEST_API_KEY);
        $this->config->method('getSenderName')->willReturn(self::TEST_SENDER_NAME);
        $this->config->method('getWebhookUrl')->willReturn(null);
        $this->service = new Delivery\AlphaSms\Service(
            $this->config,
            $this->client
        );
        Carbon::setTestNow(Carbon::createFromTimestamp(1739994599));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
    }

    public function testBatch(): void
    {
        $messages = [
            new Delivery\Message('SMS Text 1', '38093000000'),
            new Delivery\Message('Text SMS 2', '38097000000', [
                Delivery\Options::TTL => 1000,
            ]),
            new Delivery\Message('Text SMS 3', '38098000000', [
                Delivery\Options::SENDER_NAME => 'AlternateSenderName',
            ]),
            new Delivery\Message('Text SMS 4', '38098000000', [
                Delivery\AlphaSms\Options::WEBHOOK_URL => 'https://wearesho.com/alpha-sms-webhook',
            ]),
            new Delivery\Message('Viber Text 1', '38097000000', [
                Delivery\Options::CHANNEL => Delivery\AlphaSms\Service::CHANNEL_VIBER,
            ]),
            new Delivery\Message('Viber Text 2', '38097000000', [
                Delivery\Options::CHANNEL => Delivery\AlphaSms\Service::CHANNEL_VIBER,
                Delivery\Options::TTL => 2000,
            ]),
            new Delivery\Message('Viber Text 3', '38097000000', [
                Delivery\Options::CHANNEL => Delivery\AlphaSms\Service::CHANNEL_VIBER,
                Delivery\AlphaSms\Options::WEBHOOK_URL => 'https://wearesho.com/alpha-sms-webhook',
            ]),
            new Delivery\Message('Viber Text 4', '38097000000', [
                Delivery\Options::CHANNEL => Delivery\AlphaSms\Service::CHANNEL_VIBER,
                Delivery\Options::SENDER_NAME => 'ViberSender',
            ]),
        ];

        $capturedIds = [];

        $this->client
            ->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'https://alphasms.ua/api/json.php',
                $this->callback(function (array $options) use (&$capturedIds): bool {
                    $body = $options[GuzzleHttp\RequestOptions::JSON];
                    $this->assertEquals(self::TEST_API_KEY, $body['auth']);
                    $this->assertCount(8, $body['data']);

                    // Validate request structure and capture IDs
                    $expectedData = [
                        [
                            'type' => 'sms',
                            'phone' => '38093000000',
                            'sms_signature' => 'testSenderName',
                            'sms_message' => 'SMS Text 1'
                        ],
                        [
                            'type' => 'sms',
                            'phone' => '38097000000',
                            'sms_signature' => 'testSenderName',
                            'sms_message' => 'Text SMS 2',
                            'sms_lifetime' => 1000
                        ],
                        [
                            'type' => 'sms',
                            'phone' => '38098000000',
                            'sms_signature' => 'AlternateSenderName',
                            'sms_message' => 'Text SMS 3'
                        ],
                        [
                            'type' => 'sms',
                            'phone' => '38098000000',
                            'sms_signature' => 'testSenderName',
                            'sms_message' => 'Text SMS 4',
                            'hook' => 'https://wearesho.com/alpha-sms-webhook'
                        ],
                        [
                            'type' => 'viber',
                            'phone' => '38097000000',
                            'viber_type' => 'text',
                            'viber_signature' => 'testSenderName',
                            'viber_message' => 'Viber Text 1'
                        ],
                        [
                            'type' => 'viber',
                            'phone' => '38097000000',
                            'viber_type' => 'text',
                            'viber_signature' => 'testSenderName',
                            'viber_message' => 'Viber Text 2',
                            'viber_lifetime' => 2000
                        ],
                        [
                            'type' => 'viber',
                            'phone' => '38097000000',
                            'viber_type' => 'text',
                            'viber_signature' => 'testSenderName',
                            'viber_message' => 'Viber Text 3',
                            'hook' => 'https://wearesho.com/alpha-sms-webhook'
                        ],
                        [
                            'type' => 'viber',
                            'phone' => '38097000000',
                            'viber_type' => 'text',
                            'viber_signature' => 'ViberSender',
                            'viber_message' => 'Viber Text 4'
                        ],
                    ];

                    foreach ($body['data'] as $i => $item) {
                        $this->assertArrayHasKey('id', $item);
                        $this->assertStringStartsWith('i_', $item['id']);
                        $capturedIds[] = $item['id'];

                        foreach ($expectedData[$i] as $key => $value) {
                            $this->assertEquals($value, $item[$key], "Mismatch in item $i, key $key");
                        }
                    }

                    // Ensure all IDs are unique
                    $this->assertCount(count($capturedIds), array_unique($capturedIds), 'All IDs should be unique');

                    return true;
                })
            )
            ->willReturnCallback(function () use (&$capturedIds): GuzzleHttp\Psr7\Response {
                $responseData = [];
                foreach ($capturedIds as $i => $id) {
                    if ($i < 7) {
                        $responseData[] = [
                            'success' => true,
                            'data' => [
                                'id' => $id,
                                'msg_id' => 101 + $i,
                                'data' => 1,
                                'parts' => 1,
                            ],
                        ];
                    } else {
                        $responseData[] = [
                            'success' => false,
                            'error' => 'Error in Alpha-name',
                            'data' => ['id' => $id],
                        ];
                    }
                }

                return new GuzzleHttp\Psr7\Response(
                    200,
                    [],
                    json_encode(['success' => true, 'data' => $responseData])
                );
            });

        $results = [...$this->service->batch($messages)];

        $this->assertCount(8, $results);

        // Verify first 7 results are accepted
        for ($i = 0; $i < 7; $i++) {
            $this->assertEquals((string)(101 + $i), $results[$i]->messageId());
            $this->assertEquals($messages[$i], $results[$i]->message());
            $this->assertEquals(Delivery\Result\Status::Accepted, $results[$i]->status());
            $this->assertNull($results[$i]->reason());
        }

        // Verify last result is rejected
        $this->assertEquals($capturedIds[7], $results[7]->messageId());
        $this->assertEquals($messages[7], $results[7]->message());
        $this->assertEquals(Delivery\Result\Status::Rejected, $results[7]->status());
        $this->assertEquals('Error in Alpha-name', $results[7]->reason());
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
        $this->configureClient($expectedRequestBody, $mockResponseBody);

        if (!is_null($expectedException)) {
            $this->expectExceptionObject($expectedException);
        }

        $balance = $this->service->balance();
        $this->assertEquals($expectedBalance, $balance);
    }

    private function configureClient(
        array $expectedRequestBody,
        string $mockResponseBody,
        int $mockResponseStatus = 200
    ): void {
        $this->client
            ->expects($this->once())
            ->method('request')
            ->with('POST', 'https://alphasms.ua/api/json.php', [
                GuzzleHttp\RequestOptions::JSON => $expectedRequestBody,
            ])
            ->willReturn(
                new GuzzleHttp\Psr7\Response(
                    $mockResponseStatus,
                    [],
                    $mockResponseBody
                )
            );
    }
}
