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

namespace Madcoders\SyliusRmaPlugin\Twig;

use Madcoders\SyliusRmaPlugin\Services\RmaVerificationPossibilityOfReturn;
use Madcoders\SyliusRmaPlugin\Services\Withdrawal\WithdrawalEligibilityCheckerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Twig\Attribute\AsTwigFunction;

class RmaVerificationPossibilityOfReturnExtension
{
    /**
     * RmaVerificationPossibilityOfReturnExtension constructor.
     */
    public function __construct(
        private readonly RmaVerificationPossibilityOfReturn $verificationPossibilityOfReturn,
        private readonly WithdrawalEligibilityCheckerInterface $withdrawalEligibilityChecker,
    ) {
    }

    /**
     * @throws \Exception
     */
    #[AsTwigFunction(name: 'rma_order_has_items_to_returned_view')]
    public function verificationPossibilityOfReturn(OrderInterface $order): bool
    {
        return $this->verificationPossibilityOfReturn->verificationForButtonRender($order);
    }

    #[AsTwigFunction(name: 'rma_order_withdrawable_view')]
    public function verificationWithdrawable(OrderInterface $order): bool
    {
        return $this->withdrawalEligibilityChecker->isWithdrawable($order);
    }

    /**
     * Whether the customer can start any RMA process for this order - a pre-shipment withdrawal or a
     * post-shipment return. The fulfilled-state requirement is already enforced inside
     * verificationForButtonRender (it offers no return reasons unless the order is returnable).
     *
     * @throws \Exception
     */
    #[AsTwigFunction(name: 'rma_order_can_start_rma')]
    public function canStartRma(OrderInterface $order): bool
    {
        return $this->withdrawalEligibilityChecker->isWithdrawable($order) ||
            $this->verificationPossibilityOfReturn->verificationForButtonRender($order);
    }
}
