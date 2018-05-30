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
            new GuzzleHttp\Psr7\Response(200, [], 'id:1')
        );
        $message = new Delivery\Message('Some Text', '380000000000');
        $this->service->send($message);

        /** @var GuzzleHttp\Psr7\Request $request */
        $request = $this->container[0]['request'];
        $this->assertEquals(
            'https://alphasms.ua/api/http.php?to=380000000000&from=test&message=Some+Text&command=send&login=Login&pass=Password&version=http', // phpcs:ignore
            $request->getUri()->__toString()
        );
    }

    /**
     * @expectedException \Wearesho\Delivery\Exception
     * @expectedExceptionMessage Unsupported recipient format
     */
    public function testInvalidRecipient(): void
    {
        $message = new Delivery\Message("Text", "123");
        $this->service->send($message);
    }

    public function testGetBalance(): void
    {
        $this->mock->append(
            new GuzzleHttp\Psr7\Response(200, [], 'balance:1.203948')
        );
        $balance = $this->service->balance();
        $this->assertEquals('1.203948', $balance);

        /** @var GuzzleHttp\Psr7\Request $request */
        $request = $this->container[0]['request'];
        $this->assertEquals(
            'https://alphasms.ua/api/http.php?command=balance&login=Login&pass=Password&version=http',
            $request->getUri()->__toString()
        );

        $this->mock->append(new GuzzleHttp\Psr7\Response(200, [], 'balance:1.203948'));
        $this->config->apiKey = 'ApiKey';
        $this->service->balance();

        /** @var GuzzleHttp\Psr7\Request $request */
        $request = $this->container[1]['request'];
        $this->assertEquals(
            'https://alphasms.ua/api/http.php?command=balance&key=ApiKey&version=http',
            $request->getUri()->__toString()
        );
    }

    /**
     * @expectedException \Wearesho\Delivery\Exception
     * @expectedExceptionMessage Invalid Response: badbody
     */
    public function testGetBalanceInvalidResponse(): void
    {
        $this->mock->append(
            new GuzzleHttp\Psr7\Response(200, [], 'badbody')
        );

        $this->service->balance();
    }

    /**
     * @expectedException \Wearesho\Delivery\Exception
     * @expectedExceptionMessage Authorization does not configured
     */
    public function testNotConfiguredAuthorization(): void
    {
        $this->config->login = null;
        $this->config->password = null;
        $this->config->apiKey = null;

        $this->service->balance();
    }
}
