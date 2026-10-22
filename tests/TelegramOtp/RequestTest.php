<?php

declare(strict_types=1);

namespace Wearesho\Delivery\AlphaSms\Tests\TelegramOtp;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Wearesho\Delivery\AlphaSms\TelegramOtp\Request;

class RequestTest extends TestCase
{
    private int $id = 12345;
    private string $phoneNumber = '380991234567';
    private string $code = '123456';
    private int $lifetime = 300;
    private string $webhookUrl = 'https://wearesho.com/alpha-sms-webhook';

    public function testGetters(): void
    {
        $request = new Request(
            id: $this->id,
            phoneNumber: $this->phoneNumber,
            code: $this->code,
            lifetime: $this->lifetime,
            webhookUrl: $this->webhookUrl,
        );

        $this->assertSame($this->id, $request->id());
        $this->assertSame($this->phoneNumber, $request->phoneNumber());
        $this->assertSame($this->code, $request->code());
        $this->assertSame($this->lifetime, $request->lifetime());
        $this->assertSame($this->webhookUrl, $request->webhookUrl());
    }

    public function testOptionalParametersDefaultToNull(): void
    {
        $request = new Request(
            id: $this->id,
            phoneNumber: $this->phoneNumber,
            code: $this->code,
        );

        $this->assertNull($request->lifetime());
        $this->assertNull($request->webhookUrl());
    }

    public function testJsonSerialize(): void
    {
        $request = new Request(
            id: $this->id,
            phoneNumber: $this->phoneNumber,
            code: $this->code,
        );

        $expected = [
            'type' => Request::TYPE,
            'id' => $this->id,
            'phone' => (int)$this->phoneNumber,
            'message' => $this->code,
        ];

        $this->assertSame($expected, $request->jsonSerialize());

        $json = json_encode($request);
        $this->assertJson($json);
        $this->assertJsonStringEqualsJsonString(
            json_encode($expected),
            $json
        );
    }

    public function testJsonSerializeWithOptionalParameters(): void
    {
        $request = new Request(
            id: $this->id,
            phoneNumber: $this->phoneNumber,
            code: $this->code,
            lifetime: $this->lifetime,
            webhookUrl: $this->webhookUrl,
        );

        $expected = [
            'type' => Request::TYPE,
            'id' => $this->id,
            'phone' => (int)$this->phoneNumber,
            'message' => $this->code,
            'telegram_lifetime' => $this->lifetime,
            'hook' => $this->webhookUrl,
        ];

        $this->assertSame($expected, $request->jsonSerialize());
    }

    public function testCodeWithLeadingZerosIsSerializedAsString(): void
    {
        $request = new Request(
            id: $this->id,
            phoneNumber: $this->phoneNumber,
            code: '0012',
        );

        $this->assertSame('0012', $request->jsonSerialize()['message']);
        $this->assertStringContainsString('"message":"0012"', json_encode($request));
    }

    #[DataProvider('validCodeDataProvider')]
    public function testValidCode(string $code): void
    {
        $request = new Request(
            id: $this->id,
            phoneNumber: $this->phoneNumber,
            code: $code,
        );

        $this->assertSame($code, $request->code());
    }

    public static function validCodeDataProvider(): array
    {
        return [
            'min length' => ['1234'],
            'max length' => ['12345678'],
            'only zeros' => ['0000'],
        ];
    }

    #[DataProvider('invalidCodeDataProvider')]
    public function testInvalidCode(string $code): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("TelegramOTP request 'code' must contain from 4 to 8 digits.");

        new Request(
            id: $this->id,
            phoneNumber: $this->phoneNumber,
            code: $code,
        );
    }

    public static function invalidCodeDataProvider(): array
    {
        return [
            'empty' => [''],
            'too short' => ['123'],
            'too long' => ['123456789'],
            'non-digits' => ['abcd'],
            'mixed' => ['12ab'],
            'negative' => ['-1234'],
            'with spaces' => ['12 34'],
            'trailing newline' => ["1234\n"],
            'non-ascii digits' => ['١٢٣٤'],
        ];
    }

    public function testMinLifetime(): void
    {
        $request = new Request(
            id: $this->id,
            phoneNumber: $this->phoneNumber,
            code: $this->code,
            lifetime: Request::MIN_LIFETIME,
        );

        $this->assertSame(60, $request->lifetime());
    }

    #[DataProvider('invalidLifetimeDataProvider')]
    public function testInvalidLifetime(int $lifetime): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("TelegramOTP request 'lifetime' must be at least 60 seconds.");

        new Request(
            id: $this->id,
            phoneNumber: $this->phoneNumber,
            code: $this->code,
            lifetime: $lifetime,
        );
    }

    public static function invalidLifetimeDataProvider(): array
    {
        return [
            'below minimum' => [59],
            'zero' => [0],
            'negative' => [-60],
        ];
    }
}
