<?php

namespace Wearesho\Delivery\AlphaSms;

use Horat1us\Environment;

/**
 * Class EnvironmentConfig
 * @package Wearesho\Delivery\AlphaSms
 */
class EnvironmentConfig extends Environment\Config implements ConfigInterface
{
    public function __construct(string $keyPrefix = 'ALPHASMS_')
    {
        parent::__construct($keyPrefix);
    }

    public function getLogin(): ?string
    {
        return $this->getEnv('LOGIN', [$this, 'null']);
    }

    public function getPassword(): ?string
    {
        return $this->getEnv('PASSWORD', [$this, 'null']);
    }

    public function getApiKey(): ?string
    {
        return $this->getEnv('API_KEY', [$this, 'null']);
    }

    /**
     * @return string
     * @throws Environment\MissingEnvironmentException
     */
    public function getSenderName(): string
    {
        return $this->getEnv('SENDER_NAME');
    }
}
