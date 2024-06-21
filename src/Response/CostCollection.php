<?php

declare(strict_types=1);

namespace Wearesho\Delivery\AlphaSms\Response;

use Wearesho\BaseCollection;

class CostCollection extends BaseCollection
{
    public function type(): string
    {
        return Cost::class;
    }

    /**
     * @return float|int
     */
    public function sum()
    {
        $sum = 0;

        /** @var Cost $cost */
        foreach ($this as $cost) {
            $sum += $cost->getAmount();
        }

        return $sum;
    }

    public function __toString(): string
    {
        return implode(PHP_EOL, array_map(function (Cost $cost) {
            return (string)$cost;
        }, (array)$this));
    }
}
