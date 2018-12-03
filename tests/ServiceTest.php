<?php

namespace Wearesho\Delivery\AlphaSms\Tests;

use GuzzleHttp;
use PHPUnit\Framework\TestCase;
use Wearesho\Delivery;

/**
 * Class ServiceTest
 * @package Wearesho\Delivery\AlphaSms\Tests
 * @coversDefaultClass \Wearesho\Delivery\AlphaSms\Service
 */
class ServiceTest extends TestCase
{
    protected const LOGIN = 'login';
    protected const PASSWORD = 'password';
    protected const SENDER = 'test';
    protected const KEY = 'key';
    protected const RECIPIENT = '380000000000';
    protected const ERR_UNKNOWN = 200;
    protected const ERR_FORMAT = 201;

    /** @var Delivery\AlphaSms\Service */
    protected $service;

    /** @var Delivery\AlphaSms\Config */
    protected $config;

    /** @var GuzzleHttp\Handler\MockHandler */
    protected $mock;

    /** @var array */
    protected $container;

    protected function setUp(): void
    {
        parent::setUp();
        $this->config = new Delivery\AlphaSms\Config(static::LOGIN, static::PASSWORD, static::SENDER);

        $this->service = new Delivery\AlphaSms\Service($this->config, $this->createClient());
    }

    public function testConfig(): void
    {
        $this->assertEquals($this->config, $this->service->config());
    }

    public function testClient(): void
    {
        $this->assertEquals($this->createClient(), $this->service->client());
    }

    public function testSendMessage(): void
    {
        $this->mock->append(
            $this->mockSuccessResponse()
        );
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->service->send(
            new Delivery\Message('Some Text', static::RECIPIENT)
        );

        /** @var GuzzleHttp\Psr7\Request $request */
        $request = $this->container[0]['request'];
        $this->assertEquals(
            "<?xml version=\"1.0\"?>\n<package login=\"login\" password=\"password\"><message><msg recipient=\"380000000000\" sender=\"test\" type=\"0\">Some Text</msg></message></package>\n", // phpcs:ignore
            (string)$request->getBody()
        );
    }

    public function testSendWithKey(): void
    {
        $this->mock->append(
            $this->mockSuccessResponse()
        );
        $this->service = new Delivery\AlphaSms\Service(
            new Delivery\AlphaSms\Config(static::LOGIN, static::PASSWORD, null, static::KEY),
            $this->createClient()
        );
        $this->mock->append(
            $this->mockSuccessResponse()
        );
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->service->send(
            new Delivery\MessageWithSender('content', static::RECIPIENT, 'sender')
        );

        /** @var GuzzleHttp\Psr7\Request $request */
        $request = $this->container[0]['request'];
        $this->assertEquals(
            "<?xml version=\"1.0\"?>\n<package key=\"". static::KEY ."\"><message><msg recipient=\"". static::RECIPIENT . "\" sender=\"sender\" type=\"0\">content</msg></message></package>\n", // phpcs:ignore
            (string)$request->getBody()
        );
    }

    public function testBalance(): void
    {
        $expectAmount = 7.150000;
        $expectCurrency = 'UAH';
        $this->mock->append(
            $this->mockResponse("<balance><amount>$expectAmount</amount><currency>$expectCurrency</currency></balance>")
        );

        /** @noinspection PhpUnhandledExceptionInspection */
        $actualBalance = $this->service->balance();

        $this->assertEquals($expectAmount, $actualBalance->getAmount());
        $this->assertEquals($expectCurrency, $actualBalance->getCurrency());
        $this->assertEquals(
            "$expectAmount $expectCurrency",
            (string)$actualBalance
        );
    }

    public function testFailedBalance(): void
    {
        $this->expectException(Delivery\AlphaSms\Exception::class);
        $this->expectExceptionMessage("AlphaSMS Sending Error: " . static::ERR_UNKNOWN);
        $this->expectExceptionCode(static::ERR_UNKNOWN);

        $this->mock->append(
            $this->mockFailedResponse(static::ERR_UNKNOWN)
        );

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->service->balance();
    }

    /**
     * @expectedException \Wearesho\Delivery\Exception
     * @expectedExceptionMessage AlphaSMS Sending Error: 201
     * @expectedExceptionCode 201
     */
    public function testError(): void
    {
        $this->mock->append(
            $this->mockFailedResponse(static::ERR_FORMAT)
        );
        $message = new Delivery\Message('Some Text', static::RECIPIENT);
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->service->send($message);
    }

    public function testInvalidResponse(): void
    {
        $this->expectException(Delivery\Exception::class);
        $this->expectExceptionMessage(
            'Response contain invalid body: <?xml version="1.0" encoding="utf-8" ?><package><invalid</package>'
        );
        $this->expectExceptionCode(Delivery\AlphaSms\Exception::ERR_FORMAT);

        $this->mock->append(
            $this->mockResponse('<invalid')
        );

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->service->send(new Delivery\Message('content', static::RECIPIENT));
    }

    /**
     * @expectedException \Wearesho\Delivery\Exception
     * @expectedExceptionMessage Unsupported recipient format
     */
    public function testInvalidRecipient(): void
    {
        $message = new Delivery\Message("Text", "123");
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->service->send($message);
    }

    protected function createClient(): GuzzleHttp\Client
    {
        $this->mock = new GuzzleHttp\Handler\MockHandler();
        $this->container = [];
        $history = GuzzleHttp\Middleware::history($this->container);

        $stack = new GuzzleHttp\HandlerStack($this->mock);
        $stack->push($history);

        return new GuzzleHttp\Client([
            'handler' => $stack,
        ]);
    }

    protected function mockFailedResponse(int $code): GuzzleHttp\Psr7\Response
    {
        return $this->mockResponse("<error>$code</error>");
    }

    protected function mockSuccessResponse(): GuzzleHttp\Psr7\Response
    {
        return $this->mockResponse(
            '<status>
                <msg id="1234" sms_id="0" sms_count="1" date_completed="200914T15:27:03">102</msg>
                <msg sms_id="1234568" sms_count="1">1</msg>
            </status>'
        );
    }

    protected function mockResponse(string $content): GuzzleHttp\Psr7\Response
    {
        return new GuzzleHttp\Psr7\Response(
            200,
            [],
            "<?xml version=\"1.0\" encoding=\"utf-8\" ?><package>$content</package>"
        );
    }
}
