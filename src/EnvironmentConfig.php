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

    /**
     * @return string
     * @throws Environment\MissingEnvironmentException
     */
    public function getLogin(): string
    {
        return $this->getEnv('LOGIN');
    }

    /**
     * @return string
     * @throws Environment\MissingEnvironmentException
     */
    public function getPassword(): string
    {
        return $this->getEnv('PASSWORD');
    }

    /**
     * @return null|string
     * @throws Environment\MissingEnvironmentException
     */
    public function getKey(): ?string
    {
        return $this->getEnv('KEY', [$this, 'null']);
    }

    /**
     * @return string
     * @throws Environment\MissingEnvironmentException
     */
    public function getSenderName(): string
    {
        return $this->getEnv('SENDER_NAME', ConfigInterface::DEFAULT_SENDER);
    }
}
