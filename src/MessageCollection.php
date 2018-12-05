<?php

namespace Wearesho\Delivery\AlphaSms;

use Wearesho\BaseCollection;
use Wearesho\Delivery\MessageInterface;

/**
 * Class MessageCollection
 * @package Wearesho\Delivery\AlphaSms
 */
class MessageCollection extends BaseCollection
{
    public function type(): string
    {
        return MessageInterface::class;
    }
}
