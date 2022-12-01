<?php

declare(strict_types=1);

namespace Wearesho\Delivery\AlphaSms;

use Horat1us\Environment;

class EnvironmentConfig extends Environment\Config implements ConfigInterface
{
    public function __construct(string $keyPrefix = 'ALPHASMS_')
    {
        parent::__construct($keyPrefix);
    }

    /**
     * @throws Environment\MissingEnvironmentException
     */
    public function getLogin(): string
    {
        return $this->getEnv('LOGIN');
    }

    /**
     * @throws Environment\MissingEnvironmentException
     */
    public function getPassword(): string
    {
        return $this->getEnv('PASSWORD');
    }

    /**
     * @throws Environment\MissingEnvironmentException
     */
    public function getSenderName(): string
    {
        return $this->getEnv('SENDER_NAME');
    }
}
