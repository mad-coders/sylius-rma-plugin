<?php

/*
 * This file is part of package:
 * Sylius RMA Plugin
 *
 * @copyright MADCODERS Team (www.madcoders.co)
 * @licence For the full copyright and license information, please view the LICENSE
 *
 * Architects of this package:
 * @author Leonid Moshko <l.moshko@madcoders.pl>
 * @author Piotr Lewandowski <p.lewandowski@madcoders.pl>
 */

declare(strict_types=1);

namespace Madcoders\SyliusRmaPlugin\Services\Reason;

use Madcoders\SyliusRmaPlugin\Entity\OrderReturnReasonInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\ShipmentInterface;

/**
 * Decides whether a reason can be used to return an order.
 *
 * Owns the order-aware part - resolving the order's shipment and its shipped
 * date - and delegates the actual time-window decision to a
 * ReturnDeadlineCheckerInterface, which keeps the deadline rule reusable and
 * independently testable.
 */
final class ReturnReasonEligibilityChecker implements ReturnReasonEligibilityCheckerInterface
{
    public function __construct(
        private readonly ReturnDeadlineCheckerInterface $returnDeadlineChecker,
    ) {
    }

    public function isEligible(OrderInterface $order, OrderReturnReasonInterface $reason): bool
    {
        $shipment = $order->getShipments()->first();
        if (!$shipment instanceof ShipmentInterface) {
            return false;
        }

        $shippedAt = $shipment->getShippedAt();
        if (null === $shippedAt) {
            return false;
        }

        return $this->returnDeadlineChecker->isWithinDeadline($reason, $shippedAt);
    }
}
