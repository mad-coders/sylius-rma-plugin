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

interface WithdrawalEligibilityCheckerInterface
{
    /**
     * Tells whether the pre-shipment withdrawal flow is offered for the given order.
     */
    public function isWithdrawable(OrderInterface $order): bool;
}
