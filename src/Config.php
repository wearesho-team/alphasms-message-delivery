<?php

namespace Wearesho\Delivery\AlphaSms;

/**
 * Class Config
 * @package Wearesho\Delivery\AlphaSms
 */
class Config implements ConfigInterface
{
    /** @var string|null */
    public $login;

    /** @var string|null */
    public $password = null;

    /** @var string|null */
    public $apiKey = null;

    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }
}
