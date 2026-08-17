<?php

namespace App\Service;

use App\Entity\Order;
use App\Enum\OrderStatus;

class OrderStatusService
{
    /** @var array<string, list<OrderStatus>> */
    private const ALLOWED_TRANSITIONS = [
        OrderStatus::Pending->value => [OrderStatus::Paid, OrderStatus::Cancelled],
        OrderStatus::Paid->value => [OrderStatus::Shipped],
        OrderStatus::Shipped->value => [OrderStatus::Delivered],
    ];

    /** @return list<OrderStatus> */
    public function allowedTransitions(Order $order): array
    {
        return self::ALLOWED_TRANSITIONS[$order->getStatus()->value] ?? [];
    }

    public function canTransition(Order $order, OrderStatus $target): bool
    {
        return in_array($target, self::ALLOWED_TRANSITIONS[$order->getStatus()->value] ?? [], true);
    }

    public function transition(Order $order, OrderStatus $target): void
    {
        if (!$this->canTransition($order, $target)) {
            throw new \LogicException(sprintf('Transition %s -> %s interdite.', $order->getStatus()->value, $target->value));
        }

        $order->setStatus($target);
    }
}
