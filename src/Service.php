<?php

namespace Wearesho\Delivery\AlphaSms;

use Psr\Http\Message\ResponseInterface;
use Wearesho\Delivery;
use GuzzleHttp;

/**
 * Class Service
 * @package Wearesho\Delivery\AlphaSms
 */
class Service implements Delivery\ServiceInterface
{
    protected const BASE_URI = 'https://alphasms.ua/api/xml.php';

    /** @var GuzzleHttp\ClientInterface */
    protected $client;

    /** @var ConfigInterface */
    protected $config;

    public function __construct(ConfigInterface $config, GuzzleHttp\ClientInterface $client)
    {
        $this->client = $client;
        $this->config = $config;
    }

    /**
     * @param Delivery\MessageInterface $message
     *
     * @throws Delivery\Exception
     * @throws GuzzleHttp\Exception\GuzzleException
     */
    public function send(Delivery\MessageInterface $message): void
    {
        if (!preg_match('/^(\+)?380\d{9}$/', $message->getRecipient())) {
            throw new Delivery\Exception("Unsupported recipient format");
        }

        $requestObject = $this->initXmlRequestHead();
        $operation = $requestObject->addChild('message');
        $msg = $operation->addChild('msg', $message->getText());
        $msg->addAttribute('recipient', $message->getRecipient());
        $msg->addAttribute(
            'sender',
            $message instanceof Delivery\ContainsSenderName
                ? $message->getSenderName()
                : $this->config->getSenderName()
        );
        $msg->addAttribute('type', 0);

        $this->fetchBody(
            $this->client->send($this->formRequest($requestObject))
        );
    }

    /**
     * @return Response\Balance
     * @throws Delivery\Exception
     * @throws Exception
     * @throws GuzzleHttp\Exception\GuzzleException
     */
    public function balance()
    {
        $requestObject = $this->initXmlRequestHead();
        $requestObject->addChild('balance');

        $balanceXml = $this->fetchBody(
            $this->client->send($this->formRequest($requestObject))
        )->{Response\Balance::TAG};

        return new Response\Balance(
            (float)$balanceXml->{Response\Balance::AMOUNT},
            (string)$balanceXml->{Response\Balance::CURRENCY}
        );
    }

    public function config(): ConfigInterface
    {
        return $this->config;
    }

    public function client(): GuzzleHttp\ClientInterface
    {
        return $this->client;
    }

    protected function formRequest(\SimpleXMLElement $body): GuzzleHttp\Psr7\Request
    {
        return new GuzzleHttp\Psr7\Request(
            'GET',
            static::BASE_URI,
            ['Content-Type' => 'application/xml',],
            $body->saveXML()
        );
    }

    /**
     * @param ResponseInterface $response
     *
     * @return \SimpleXMLElement
     * @throws Delivery\Exception
     * @throws Exception
     */
    protected function fetchBody(ResponseInterface $response): \SimpleXMLElement
    {
        $body = (string)$response->getBody();

        try {
            $xml = simplexml_load_string($body);
        } catch (\Throwable $exception) {
            throw new Delivery\Exception("Response contain invalid body: " . $body, Exception::ERR_FORMAT, $exception);
        }

        if ($xml->error) {
            $errorCode = $xml->error[0]->__toString();
            throw new Exception(
                "AlphaSMS Sending Error: " . $errorCode,
                $errorCode
            );
        }

        return $xml;
    }

    protected function initXmlRequestHead(): \SimpleXMLElement
    {
        $requestObject = new \SimpleXMLElement('<package></package>');

        $key = $this->config->getApiKey();

        if (!empty($key)) {
            $requestObject->addAttribute('key', $key);
        } else {
            $requestObject->addAttribute('login', $this->config->getLogin());
            $requestObject->addAttribute('password', $this->config->getPassword());
        }

        return $requestObject;
    }
}
