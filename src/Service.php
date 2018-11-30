<?php

namespace Wearesho\Delivery\AlphaSms;

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

        $response = $this->client->send($this->formRequest($requestObject));
        $body = $response->getBody()->__toString();
        $xml = simplexml_load_string($body);
        $this->validateResponse($xml);
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

        $response = $this->client->send($this->formRequest($requestObject));

        $body = (string)$response->getBody();
        $xml = simplexml_load_string($body);
        $this->validateResponse($xml);
        $balanceXml = $xml->{Response\Balance::TAG};

        return new Response\Balance(
            (float)$balanceXml->{Response\Balance::AMOUNT},
            (string)$balanceXml->{Response\Balance::CURRENCY}
        );
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
     * @param \SimpleXMLElement $response
     *
     * @throws Exception|Delivery\Exception
     */
    protected function validateResponse(\SimpleXMLElement $response)
    {
        if ($response->error) {
            $errorCode = $response->error[0]->__toString();
            throw new Exception(
                "AlphaSMS Sending Error: " . $errorCode,
                $errorCode
            );
        }
    }

    protected function initXmlRequestHead(): \SimpleXMLElement
    {
        $requestObject = new \SimpleXMLElement('<package></package>');

        $requestObject->addAttribute('login', $this->config->getLogin());
        $requestObject->addAttribute('password', $this->config->getPassword());

        return $requestObject;
    }
}
