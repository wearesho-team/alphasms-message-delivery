<?php

namespace Wearesho\Delivery\AlphaSms;

class Config implements ConfigInterface
{
    public string $sender = 'test';

    public ?string $login = null;

    public ?string $password = null;

    public ?string $apiKey = null;

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
