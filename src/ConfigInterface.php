<?php

namespace Wearesho\Delivery\AlphaSms;

/**
 * Interface ConfigInterface
 * @package Wearesho\Delivery\AlphaSms
 */
interface ConfigInterface
{
    public const DEFAULT_SENDER = 'system';

    public function getSenderName(): string;

    public function getLogin(): string;

    public function getPassword(): string;

    public function getKey(): ?string;
}
