<?php

namespace Wearesho\Delivery\AlphaSms;

/**
 * Class Config
 * @package Wearesho\Delivery\AlphaSms
 */
class Config implements ConfigInterface
{
    public $sender;

    /** @var string|null */
    public $login;

    /** @var string|null */
    public $password;

    /** @var string|null */
    public $apiKey;

    public function __construct(
        string $login,
        string $password,
        ?string $senderName = ConfigInterface::DEFAULT_SENDER,
        string $key = null
    ) {
        $this->login = $login;
        $this->password = $password;
        $this->sender = $senderName;
        $this->apiKey = $key;
    }

    public function getSenderName(): string
    {
        return $this->sender;
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }
}
