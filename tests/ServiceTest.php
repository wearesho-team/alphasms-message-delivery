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
        $this->config = new Delivery\AlphaSms\Config();
        $this->config->login = 'Login';
        $this->config->password = 'Password';

        $this->mock = new GuzzleHttp\Handler\MockHandler();
        $this->container = [];
        $history = GuzzleHttp\Middleware::history($this->container);

        $stack = new GuzzleHttp\HandlerStack($this->mock);
        $stack->push($history);

        $this->service = new Delivery\AlphaSms\Service($this->config, new GuzzleHttp\Client([
            'handler' => $stack,
        ]));
    }

    public function testSendMessage(): void
    {
        $this->mock->append(
            new GuzzleHttp\Psr7\Response(200, [], '<?xml version="1.0" encoding="utf-8" ?><package><status><msg id="1234" sms_id="0" sms_count="1" date_completed="200914T15:27:03">102</msg><msg sms_id="1234568" sms_count="1">1</msg></status></package>') // phpcs:ignore
        );
        $message = new Delivery\Message('Some Text', '380000000000');
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->service->send($message);

        /** @var GuzzleHttp\Psr7\Request $request */
        $request = $this->container[0]['request'];
        $this->assertEquals(
            '<?xml version="1.0"?>
<package login="Login" password="Password"><message><msg recipient="380000000000" sender="test" type="0">Some Text</msg></message></package>' // phpcs:ignore
            . '
',
            (string)$request->getBody()
        );
    }

    public function testBalance(): void
    {
        $expectAmount = 7.15;
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
        $message = new Delivery\Message('Some Text', '380000000000');
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->service->send($message);
    }

    public function testCostCollection(): void
    {
        $this->mock->append(
            $this->mockResponse(
                '<prices>
                    <phone price="0.28" currency="UAH">380501234567</phone>
                    <phone price="1.6" currency="UAH">37122123456</phone>
                </prices>'
            )
        );

        /** @noinspection PhpUnhandledExceptionInspection */
        $costs = $this->service->cost([
            '380501234567',
            '37122123456'
        ]);

        $this->assertEquals($costs[0]->getRecipient(), '380501234567');
        $this->assertEquals($costs[0]->getAmount(), 0.28);
        $this->assertEquals($costs[1]->getRecipient(), '37122123456');
        $this->assertEquals($costs[1]->getAmount(), 1.6);
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
        $this->service->send(new Delivery\Message('content', '380000000000'));
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

    protected function mockFailedResponse(int $code): GuzzleHttp\Psr7\Response
    {
        return $this->mockResponse("<error>$code</error>");
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
