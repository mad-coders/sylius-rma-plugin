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
 * Resolves the pre-shipment withdrawal path for an order from its order/payment/shipping state.
 *
 * Only a placed-but-not-yet-fulfilled order (OrderInterface::STATE_NEW) that has not shipped is
 * withdrawable; this excludes carts, cancelled orders, and shipped/fulfilled orders, leaving the
 * post-shipment return flow untouched. A paid or authorized order takes the admin cancellation
 * request path; anything else takes the unpaid auto-cancel path when the feature flag is on.
 */
final readonly class WithdrawalEligibilityChecker implements WithdrawalEligibilityCheckerInterface
{
    public function __construct(
        private bool $allowUnpaidWithdrawal = true,
    ) {
    }

    public function resolvePath(OrderInterface $order): WithdrawalPath
    {
        // An order still in checkout (a cart) is never withdrawable.
        if (OrderCheckoutStates::STATE_CART === $order->getCheckoutState()) {
            return WithdrawalPath::NONE;
        }

        if (OrderInterface::STATE_NEW !== $order->getState()) {
            return WithdrawalPath::NONE;
        }

        if (in_array($order->getShippingState(), [
            OrderShippingStates::STATE_SHIPPED,
            OrderShippingStates::STATE_PARTIALLY_SHIPPED,
        ], true)) {
            return WithdrawalPath::NONE;
        }

        if (in_array($order->getPaymentState(), [
            OrderPaymentStates::STATE_PAID,
            OrderPaymentStates::STATE_AUTHORIZED,
        ], true)) {
            return WithdrawalPath::PAID_REQUEST;
        }

        return $this->allowUnpaidWithdrawal ? WithdrawalPath::UNPAID_AUTOCANCEL : WithdrawalPath::NONE;
    }
}
