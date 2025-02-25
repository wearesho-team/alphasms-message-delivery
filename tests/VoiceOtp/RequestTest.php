<?php

declare(strict_types=1);

namespace Wearesho\Delivery\AlphaSms\Tests\VoiceOtp;

use PHPUnit\Framework\TestCase;
use Wearesho\Delivery\AlphaSms\VoiceOtp\Request;

class RequestTest extends TestCase
{
    private Request $request;
    private int $id = 12345;
    private string $phoneNumber = '380991234567';

    protected function setUp(): void
    {
        $this->request = new Request(
            id: $this->id,
            phoneNumber: $this->phoneNumber
        );
    }

    public function testId(): void
    {
        $this->assertSame($this->id, $this->request->id());
    }

    public function testPhoneNumber(): void
    {
        $this->assertSame($this->phoneNumber, $this->request->phoneNumber());
    }

    public function testJsonSerialize(): void
    {
        $expected = [
            'type' => Request::TYPE,
            'id' => $this->id,
            'phone' => (int)$this->phoneNumber,
        ];

        $this->assertSame($expected, $this->request->jsonSerialize());

        $json = json_encode($this->request);
        $this->assertJson($json);
        $this->assertJsonStringEqualsJsonString(
            json_encode($expected),
            $json
        );
    }
}
