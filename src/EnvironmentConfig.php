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
    public function getApiKey(): string
    {
        return $this->getEnv("API_KEY");
    }

    /**
     * @throws Environment\MissingEnvironmentException
     */
    public function getSenderName(): string
    {
        return $this->getEnv('SENDER_NAME');
    }

    public function getWebhookUrl(): ?string
    {
        return $this->getEnv('WEBHOOK_URL', static fn() => null);
    }
}
