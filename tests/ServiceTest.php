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

    public static function batchDataProvider(): array
    {
        return [
            [
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
                ],
                [
                    'auth' => '96ac8bf5-4347-4c48-aa8e-1147aecc3bd2',
                    'data' => [
                        [
                            'type' => 'sms',
                            'id' => 'i_1739994599_0',
                            'phone' => '38093000000',
                            'sms_signature' => 'testSenderName',
                            'sms_message' => 'SMS Text 1',
                        ],
                        [
                            'type' => 'sms',
                            'id' => 'i_1739994599_1',
                            'phone' => '38097000000',
                            'sms_signature' => 'testSenderName',
                            'sms_message' => "Text SMS 2",
                            'sms_lifetime' => 1000,
                        ],
                        [
                            'type' => 'sms',
                            'id' => 'i_1739994599_2',
                            'phone' => '38098000000',
                            'sms_signature' => 'AlternateSenderName',
                            'sms_message' => 'Text SMS 3',
                        ],
                        [
                            'type' => 'sms',
                            'id' => 'i_1739994599_3',
                            'phone' => '38098000000',
                            'sms_signature' => 'testSenderName',
                            'sms_message' => 'Text SMS 4',
                            'hook' => 'https://wearesho.com/alpha-sms-webhook',
                        ],
                        [
                            'type' => 'viber',
                            'id' => 'i_1739994599_4',
                            'phone' => '38097000000',
                            'viber_type' => 'text',
                            'viber_signature' => 'testSenderName',
                            'viber_message' => 'Viber Text 1',
                        ],
                        [
                            'type' => 'viber',
                            'id' => 'i_1739994599_5',
                            'phone' => '38097000000',
                            'viber_type' => 'text',
                            'viber_signature' => 'testSenderName',
                            'viber_message' => 'Viber Text 2',
                            'viber_lifetime' => 2000,
                        ],
                        [
                            'type' => 'viber',
                            'id' => 'i_1739994599_6',
                            'phone' => '38097000000',
                            'viber_type' => 'text',
                            'viber_signature' => 'testSenderName',
                            'viber_message' => 'Viber Text 3',
                            'hook' => 'https://wearesho.com/alpha-sms-webhook',
                        ],
                        [
                            'type' => 'viber',
                            'id' => 'i_1739994599_7',
                            'phone' => '38097000000',
                            'viber_type' => 'text',
                            'viber_signature' => 'ViberSender',
                            'viber_message' => 'Viber Text 4',
                        ],
                    ],
                ],
                <<<JSON
{
    "success": true,
    "data": [
        {
            "success": true,
            "data": {
                "id": "i_1739994599_0",
                "msg_id": 101,
                "data": 1,
                "parts": 1
            }
        },
        {
            "success": true,
            "data": {
                "id": "i_1739994599_1",
                "msg_id": 102,
                "data": 1,
                "parts": 1
            }
        },
        {
            "success": true,
            "data": {
                "id": "i_1739994599_2",
                "msg_id": 103,
                "data": 1,
                "parts": 1
            }
        },
        {
            "success": true,
            "data": {
                "id": "i_1739994599_3",
                "msg_id": 104,
                "data": 1,
                "parts": 1
            }
        },
        {
            "success": true,
            "data": {
                "id": "i_1739994599_4",
                "msg_id": 105,
                "data": 1,
                "parts": 1
            }
        },
        {
            "success": true,
            "data": {
                "id": "i_1739994599_5",
                "msg_id": 106,
                "data": 1,
                "parts": 1
            }
        },
        {
            "success": true,
            "data": {
                "id": "i_1739994599_6",
                "msg_id": 107,
                "data": 1,
                "parts": 1
            }
        },
        {
            "success": false,
            "error": "Error in Alpha-name",
            "data": {
                "id": "i_1739994599_7"
            }
        }
    ]
}
JSON,
                array_map(
                    fn(int $i) => new Delivery\Result(
                        ($i < 7)
                            ? (string)(101 + $i)
                            : ("i_1739994599_" . $i),
                        $messages[$i],
                        ($i < 7)
                            ? Delivery\Result\Status::Accepted
                            : Delivery\Result\Status::Rejected,
                        ($i < 7) ? null : "Error in Alpha-name"
                    ),
                    range(0, 7),
                )
            ]
        ];
    }

    #[DataProvider('batchDataProvider')]
    public function testBatch(
        array $messages,
        array $expectedRequestBody,
        string $mockResponseBody,
        ?array $expectedResults = null,
        ?Delivery\Exception $expectedException = null
    ): void {
        $this->configureClient($expectedRequestBody, $mockResponseBody);

        if (!is_null($expectedException)) {
            $this->expectExceptionObject($expectedException);
        }

        $results = [...$this->service->batch($messages)];
        if (!is_null($expectedResults)) {
            $this->assertEquals($expectedResults, $results);
        }
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
            ->willReturn(new GuzzleHttp\Psr7\Response(
                $mockResponseStatus,
                [],
                $mockResponseBody
            ));
    }
}
