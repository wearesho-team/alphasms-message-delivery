<?php

declare(strict_types=1);

namespace Wearesho\Delivery\AlphaSms;

interface ConfigInterface
{
    public function getSenderName(): string;

    public function getApiKey(): string;

    public function getWebhookUrl(): ?string;
}
