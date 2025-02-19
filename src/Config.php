<?php

declare(strict_types=1);

namespace Wearesho\Delivery\AlphaSms;

class Config implements ConfigInterface
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $senderName,
    ) {
    }

    public function getSenderName(): string
    {
        return $this->senderName;
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }
}
