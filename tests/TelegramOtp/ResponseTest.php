<?php

declare(strict_types=1);

namespace Wearesho\Delivery\AlphaSms\Tests\TelegramOtp;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Wearesho\Delivery\AlphaSms\TelegramOtp\Response;
use Wearesho\Delivery\Exception;

class ResponseTest extends TestCase
{
    private int $validId = 100500;
    private int $validMessageId = 123456789;

    public function testParse(): void
    {
        $response = Response::parse([
            'id' => $this->validId,
            'msg_id' => $this->validMessageId,
            'data' => 1,
            'parts' => 1,
        ]);

        $this->assertSame($this->validId, $response->id());
        $this->assertSame((string)$this->validMessageId, $response->messageId());
    }

    public function testParseWithNumericStrings(): void
    {
        $response = Response::parse([
            'id' => (string)$this->validId,
            'msg_id' => (string)$this->validMessageId,
        ]);

        $this->assertSame($this->validId, $response->id());
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
            'missing id' => [
                ['msg_id' => 123456789],
                "Missing required 'id' field in TelegramOTP response."
            ],
            'null id' => [
                ['id' => null, 'msg_id' => 123456789],
                "TelegramOTP response 'id' must be an integer."
            ],
            'float id' => [
                ['id' => 100500.5, 'msg_id' => 123456789],
                "TelegramOTP response 'id' must be an integer."
            ],
            'non-numeric id' => [
                ['id' => 'abc', 'msg_id' => 123456789],
                "TelegramOTP response 'id' must be an integer."
            ],
            'missing msg_id' => [
                ['id' => 100500],
                "Missing required 'msg_id' field in TelegramOTP response."
            ],
            'null msg_id' => [
                ['id' => 100500, 'msg_id' => null],
                "TelegramOTP response 'msg_id' must be an integer."
            ],
            'non-numeric msg_id' => [
                ['id' => 100500, 'msg_id' => 'abc'],
                "TelegramOTP response 'msg_id' must be an integer."
            ],
            'array msg_id' => [
                ['id' => 100500, 'msg_id' => [123456789]],
                "TelegramOTP response 'msg_id' must be an integer."
            ],
        ];
    }

    public function testJsonSerialize(): void
    {
        $response = Response::parse([
            'id' => $this->validId,
            'msg_id' => $this->validMessageId,
        ]);

        $expected = [
            'id' => $this->validId,
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
