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

interface ReturnDeadlineCheckerInterface
{
    /**
     * Whether a return for the given reason is still allowed for a shipment sent at $shippedAt.
     */
    public function isWithinDeadline(OrderReturnReasonInterface $reason, \DateTimeInterface $shippedAt): bool;
}
