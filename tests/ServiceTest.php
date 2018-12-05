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
    protected const LOGIN = 'Login';
    protected const PASSWORD = 'Password';
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
        $this->config = new Delivery\AlphaSms\Config();
        $this->config->login = static::LOGIN;
        $this->config->password = static::PASSWORD;

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
            new GuzzleHttp\Psr7\Response(
                200,
                [],
                '<?xml version="1.0" encoding="utf-8" ?><package><status><msg id="1234" sms_id="0" sms_count="1" date_completed="200914T15:27:03">102</msg><msg sms_id="1234568" sms_count="1">1</msg></status></package>'  // phpcs:ignore
            )
        );
        $message = new Delivery\Message('Some Text', static::RECIPIENT);
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
        $expectAmount = 1200.15;
        $expectCurrency = 'UAH';
        $this->mock->append(
            $this->mockResponse("<balance><amount>$expectAmount</amount><currency>$expectCurrency</currency></balance>")
        );

        /** @noinspection PhpUnhandledExceptionInspection */
        $actualBalance = $this->service->balance();

        $this->assertEquals($expectAmount, $actualBalance->getAmount());
        $this->assertEquals($expectCurrency, $actualBalance->getCurrency());
        $this->assertEquals(
            "1,200.15 $expectCurrency",
            (string)$actualBalance
        );
        $this->assertEquals(
            [
                'amount' => $expectAmount,
                'currency' => $expectCurrency,
            ],
            $actualBalance->jsonSerialize()
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

    public function testSendPatch(): void
    {
        $this->mock->append(
            $this->mockResponse(
                '<message>
                    <msg sms_id="85244344" sms_count="1">1</msg>
                    <msg sms_id="0" sms_count="0">202</msg>
                </message>'
            )
        );

        $messageCollection = new Delivery\AlphaSms\MessageCollection([
            new Delivery\Message('Test', static::RECIPIENT),
            new Delivery\Message('Test', static::RECIPIENT),
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $statusCollection = $this->service->sendPatch($messageCollection);

        /** @var Delivery\AlphaSms\Response\MessageStatus $firstStatus */
        $firstStatus = $statusCollection[0];

        $this->assertEquals('85244344', $firstStatus->getGatewayId());
        $this->assertEquals(1, $firstStatus->getSmsCount());
        $this->assertEquals(1, $firstStatus->getValue());
        $this->assertNull($firstStatus->getId());
        $this->assertTrue($firstStatus->isSuccess());

        /** @var Delivery\AlphaSms\Response\MessageStatus $secondStatus */
        $secondStatus = $statusCollection[1];

        $this->assertEquals('0', $secondStatus->getGatewayId());
        $this->assertEquals(0, $secondStatus->getSmsCount());
        $this->assertEquals(202, $secondStatus->getValue());
        $this->assertNull($secondStatus->getId());
        $this->assertFalse($secondStatus->isSuccess());
    }

    public function testFailedSendPatch(): void
    {
        $this->expectException(Delivery\Exception::class);
        $this->expectExceptionMessage('Unsupported recipient format');
        $this->expectExceptionCode(0);

        $messageCollection = new Delivery\AlphaSms\MessageCollection([
            new Delivery\Message('Test', 'invalid_recipient'),
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->service->sendPatch($messageCollection);
    }

    public function testSendPatchAsync(): void
    {
        $expectJobId = '8714ea91e9bb454d25ef42ba6f18ea4e';

        $this->mock->append(
            $this->mockResponse(
                "<message-async>
                    <job>$expectJobId</job>
                </message-async>"
            )
        );

        $messageCollection = new Delivery\AlphaSms\MessageCollection([
            new Delivery\Message('Test', static::RECIPIENT),
            new Delivery\Message('Test', static::RECIPIENT),
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $actualJobId = $this->service->sendPatchAsync($messageCollection);
        $this->assertEquals($expectJobId, $actualJobId);
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

        /** @var GuzzleHttp\Psr7\Request $request */
        $request = $this->container[0]['request'];
        $this->assertEquals(
            "<?xml version=\"1.0\"?>\n<package login=\"Login\" password=\"Password\"><prices><phone>380501234567</phone><phone>37122123456</phone></prices></package>\n", // phpcs:ignore
            (string)$request->getBody()
        );
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
