<?php

declare(strict_types=1);

namespace Wearesho\Delivery\AlphaSms\Tests\VoiceOtp;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Wearesho\Delivery\AlphaSms\VoiceOtp\Response;
use Wearesho\Delivery\Exception;

class ResponseTest extends TestCase
{
    private string $validCode = '1234';
    private float $validPrice = 0.25;

    public function testParse(): void
    {
        $responseData = [
            'code' => $this->validCode,
            'price' => $this->validPrice,
        ];

        $response = Response::parse($responseData);

        $this->assertSame($this->validCode, $response->code());
        $this->assertSame($this->validPrice, $response->price());
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
            'missing code' => [
                ['price' => 0.25],
                "Missing required 'code' field in VoiceOTP response."
            ],
            'non-string code' => [
                ['code' => 1234, 'price' => 0.25],
                "VoiceOTP response 'code' must be a string."
            ],
            'code too short' => [
                ['code' => '123', 'price' => 0.25],
                "VoiceOTP response 'code' must be exactly 4 digits."
            ],
            'code too long' => [
                ['code' => '12345', 'price' => 0.25],
                "VoiceOTP response 'code' must be exactly 4 digits."
            ],
            'code with non-digits' => [
                ['code' => 'abcd', 'price' => 0.25],
                "VoiceOTP response 'code' must be exactly 4 digits."
            ],
            'missing price' => [
                ['code' => '1234'],
                "Missing required 'price' field in VoiceOTP response."
            ],
            'non-numeric price' => [
                ['code' => '1234', 'price' => 'not-a-number'],
                "VoiceOTP response 'price' must be a numeric value."
            ],
            'negative price' => [
                ['code' => '1234', 'price' => -1.0],
                "VoiceOTP response 'price' cannot be negative."
            ],
        ];
    }

    public function testJsonSerialize(): void
    {
        $response = Response::parse([
            'code' => $this->validCode,
            'price' => $this->validPrice,
        ]);

        $expected = [
            'code' => $this->validCode,
            'price' => $this->validPrice,
        ];

        $this->assertSame($expected, $response->jsonSerialize());

        $json = json_encode($response);
        $this->assertJson($json);
        $this->assertJsonStringEqualsJsonString(
            json_encode($expected),
            $json
        );
    }

    public function testGetters(): void
    {
        $response = Response::parse([
            'code' => $this->validCode,
            'price' => $this->validPrice,
        ]);

        $this->assertSame($this->validCode, $response->code());
        $this->assertSame($this->validPrice, $response->price());
    }
}
