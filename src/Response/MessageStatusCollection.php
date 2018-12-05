<?php

namespace Wearesho\Delivery\AlphaSms\Response;

use Wearesho\BaseCollection;

/**
 * Class MessageStatusCollection
 * @package Wearesho\Delivery\AlphaSms\Response
 */
class MessageStatusCollection extends BaseCollection
{
    public function type(): string
    {
        return MessageStatus::class;
    }

    public function totalSmsCount(): int
    {
        return array_sum(array_map(function (MessageStatus $status): int {
            return $status->getSmsCount();
        }, (array)$this));
    }
}
