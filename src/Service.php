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

        $response = $this->client->request('get', static::BASE_URI, [
            GuzzleHttp\RequestOptions::HEADERS => [
                'Content-Type' => 'application/xml',
            ],
            GuzzleHttp\RequestOptions::BODY => $requestObject->saveXML(),
        ]);

        $body = $response->getBody()->__toString();

        $xml = simplexml_load_string($body);
        if ($xml->error) {
            $errorCode = $xml->error[0]->__toString();
            throw new Delivery\Exception(
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
