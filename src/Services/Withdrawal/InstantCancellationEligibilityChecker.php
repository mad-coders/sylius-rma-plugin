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
use Sylius\Component\Core\OrderPaymentStates;

/**
 * Decides whether a (already withdrawable) order qualifies for instant withdrawal - the fast-forward
 * that cancels the order and resolves the return straight to "withdrawn" with no admin step.
 *
 * An order is instantly withdrawable when it is not paid: there is no money to refund, so no admin
 * review is needed. A paid or authorized order instead goes through the admin-approved withdrawal.
 * This checker is only consulted after {@see WithdrawalEligibilityChecker::isWithdrawable()} passes,
 * so the allow_unpaid_withdrawal gating lives there, not here.
 */
final readonly class InstantCancellationEligibilityChecker implements InstantCancellationEligibilityCheckerInterface
{
    public function isEligible(OrderInterface $order): bool
    {
        return !in_array($order->getPaymentState(), [
            OrderPaymentStates::STATE_PAID,
            OrderPaymentStates::STATE_AUTHORIZED,
        ], true);
    }
}
