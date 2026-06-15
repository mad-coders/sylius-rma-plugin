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

namespace Madcoders\SyliusRmaPlugin\Services\Withdrawal;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\OrderCheckoutStates;
use Sylius\Component\Core\OrderPaymentStates;
use Sylius\Component\Core\OrderShippingStates;

/**
 * Decides whether the pre-shipment withdrawal flow is offered for an order from its
 * order/payment/shipping state.
 *
 * Only a placed-but-not-yet-fulfilled order (OrderInterface::STATE_NEW) that has not shipped is
 * withdrawable; this excludes carts, cancelled orders, and shipped/fulfilled orders, leaving the
 * post-shipment return flow untouched. A paid or authorized order is always withdrawable; an unpaid
 * order is withdrawable only when the allow_unpaid_withdrawal flag is on. Whether a withdrawable
 * order is withdrawn instantly or via admin approval is a separate decision made by
 * {@see InstantCancellationEligibilityChecker}.
 */
final readonly class WithdrawalEligibilityChecker implements WithdrawalEligibilityCheckerInterface
{
    public function __construct(
        private bool $allowUnpaidWithdrawal = true,
    ) {
    }

    public function isWithdrawable(OrderInterface $order): bool
    {
        // An order still in checkout (a cart) is never withdrawable.
        if (OrderCheckoutStates::STATE_CART === $order->getCheckoutState()) {
            return false;
        }

        if (OrderInterface::STATE_NEW !== $order->getState()) {
            return false;
        }

        if (in_array($order->getShippingState(), [
            OrderShippingStates::STATE_SHIPPED,
            OrderShippingStates::STATE_PARTIALLY_SHIPPED,
        ], true)) {
            return false;
        }

        if (in_array($order->getPaymentState(), [
            OrderPaymentStates::STATE_PAID,
            OrderPaymentStates::STATE_AUTHORIZED,
        ], true)) {
            return true;
        }

        // Unpaid order: only withdrawable when the feature flag allows it.
        return $this->allowUnpaidWithdrawal;
    }
}
