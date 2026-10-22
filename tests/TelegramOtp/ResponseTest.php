<?php

declare(strict_types=1);

namespace Wearesho\Delivery\AlphaSms\Tests\TelegramOtp;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Wearesho\Delivery\AlphaSms\TelegramOtp\Response;
use Wearesho\Delivery\Exception;

class ResponseTest extends TestCase
{
    private int $validMessageId = 1590809016;

    public function testParse(): void
    {
        // Actual response data returned by API
        $response = Response::parse([
            'msg_id' => $this->validMessageId,
        ]);

        $this->assertSame((string)$this->validMessageId, $response->messageId());
    }

    public function testParseWithDocumentedResponseData(): void
    {
        $response = Response::parse([
            'id' => 100500,
            'msg_id' => $this->validMessageId,
            'data' => 1,
            'parts' => 1,
        ]);

        $this->assertSame((string)$this->validMessageId, $response->messageId());
    }

    public function testParseWithNumericString(): void
    {
        $response = Response::parse([
            'msg_id' => (string)$this->validMessageId,
        ]);

        $this->assertSame((string)$this->validMessageId, $response->messageId());
    }

    #[DataProvider('invalidResponseDataProvider')]
    public function testParseWithInvalidData(array $responseData, string $expectedExceptionMessage): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        Response::parse($responseData);
    }

    public static function invalidResponseDataProvider(): array
    {
        return [
            'missing msg_id' => [
                ['id' => 100500],
                "Missing required 'msg_id' field in TelegramOTP response."
            ],
            'empty data' => [
                [],
                "Missing required 'msg_id' field in TelegramOTP response."
            ],
            'null msg_id' => [
                ['msg_id' => null],
                "TelegramOTP response 'msg_id' must be an integer."
            ],
            'float msg_id' => [
                ['msg_id' => 1590809016.5],
                "TelegramOTP response 'msg_id' must be an integer."
            ],
            'non-numeric msg_id' => [
                ['msg_id' => 'abc'],
                "TelegramOTP response 'msg_id' must be an integer."
            ],
            'array msg_id' => [
                ['msg_id' => [1590809016]],
                "TelegramOTP response 'msg_id' must be an integer."
            ],
        ];
    }

    public function testJsonSerialize(): void
    {
        $response = Response::parse([
            'msg_id' => $this->validMessageId,
        ]);

        $expected = [
            'messageId' => (string)$this->validMessageId,
        ];

        $this->assertSame($expected, $response->jsonSerialize());

        $json = json_encode($response);
        $this->assertJson($json);
        $this->assertJsonStringEqualsJsonString(
            json_encode($expected),
            $json
        );
    }
}
