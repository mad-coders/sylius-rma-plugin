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

interface InstantCancellationEligibilityCheckerInterface
{
    /**
     * Tells whether a withdrawable order can be withdrawn instantly (fast-forward to "withdrawn"
     * with the order cancelled) instead of going through the admin approval step.
     */
    public function isEligible(OrderInterface $order): bool;
}
