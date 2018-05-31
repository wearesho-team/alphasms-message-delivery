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

        $this->client->request('get', static::BASE_URI, [
            GuzzleHttp\RequestOptions::HEADERS => [
                'Content-Type' => 'application/xml',
            ],
            GuzzleHttp\RequestOptions::BODY => $requestObject->saveXML(),
        ]);
    }

    /**
     * @return float
     * @throws Delivery\Exception
     * @throws GuzzleHttp\Exception\GuzzleException
     */
    public function balance(): float
    {
        $params = [
            'command' => Command::BALANCE,
        ];
        $response = $this->client->request('get', $this->buildQuery($params));
        $body = (string)$response->getBody();

        if (!preg_match('/^balance:(\d+(\.\d+)?)$/', (string)$response->getBody(), $matches)) {
            throw new Delivery\Exception("Invalid Response: $body");
        }

        return $matches[1];
    }

    protected function initXmlRequestHead(): \SimpleXMLElement
    {
        $requestObject = new \SimpleXMLElement('<package></package>');

        $requestObject->addAttribute('login', $this->config->getLogin());
        $requestObject->addAttribute('password', $this->config->getPassword());

        return $requestObject;
    }

    /**
     * @param array $params
     * @throws Delivery\Exception
     * @return string
     */
    protected function buildQuery(array $params): string
    {
        $apiKey = $this->config->getApiKey();
        if ($this->config->getApiKey()) {
            $params['key'] = $apiKey;
        } else {
            $login = $this->config->getLogin();
            $password = $this->config->getPassword();

            if (empty($login) || empty($password)) {
                throw new Delivery\Exception("Authorization does not configured");
            }

            $params += [
                'login' => $login,
                'password' => $password,
            ];
        }

        $params['version'] = 'http';

        return static::BASE_URI . '?' . http_build_query($params);
    }
}
