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

namespace Madcoders\SyliusRmaPlugin\Services;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\OrderCheckoutStates;

/**
 * Resolves whether an order qualifies for the post-shipment return flow from its order state.
 *
 * Only a fulfilled order is returnable; carts, placed-but-not-yet-fulfilled orders and cancelled
 * orders are not. This is the post-shipment counterpart to the pre-shipment withdrawal decision made
 * by {@see \Madcoders\SyliusRmaPlugin\Services\Withdrawal\WithdrawalEligibilityChecker}. Whether the
 * order still has items left to return is a finer, separate concern handled by
 * {@see RmaVerificationPossibilityOfReturn}.
 */
final readonly class ReturnEligibilityChecker implements ReturnEligibilityCheckerInterface
{
    public function isReturnable(OrderInterface $order): bool
    {
        // An order still in checkout (a cart) is never returnable.
        if (OrderCheckoutStates::STATE_CART === $order->getCheckoutState()) {
            return false;
        }

        return OrderInterface::STATE_FULFILLED === $order->getState();
    }
}
