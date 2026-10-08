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
 * Owns the order-aware part - resolving the order's shipment, its shipped date and any grace
 * period granted for this order and reason (#67) - and delegates the actual time-window
 * decision to a ReturnDeadlineCheckerInterface, which keeps the deadline rule reusable and
 * independently testable.
 *
 * A grace period only extends the deadline: the shipment checks run first, so an order that
 * was not shipped stays ineligible whatever grace it has. A replaced deadline checker that does
 * not implement GraceAwareReturnDeadlineCheckerInterface keeps the base deadline.
 */
final readonly class ReturnReasonEligibilityChecker implements ReturnReasonEligibilityCheckerInterface
{
    public function __construct(
        private ReturnDeadlineCheckerInterface $returnDeadlineChecker,
        private ?ReturnReasonGracePeriodResolverInterface $gracePeriodResolver = null,
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

        $graceDays = $this->gracePeriodResolver?->getExtraDays($order, $reason) ?? 0;
        if ($graceDays > 0 && $this->returnDeadlineChecker instanceof GraceAwareReturnDeadlineCheckerInterface) {
            return $this->returnDeadlineChecker->isWithinDeadlineWithGrace($reason, $shippedAt, $graceDays);
        }

        return $this->returnDeadlineChecker->isWithinDeadline($reason, $shippedAt);
    }
}
