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

/**
 * A deadline checker that can extend a reason's deadline by a per-order grace period (#67).
 *
 * Kept separate from ReturnDeadlineCheckerInterface so applications that replaced the deadline
 * checker keep working unchanged; their checker simply does not apply grace periods until it
 * implements this interface.
 */
interface GraceAwareReturnDeadlineCheckerInterface extends ReturnDeadlineCheckerInterface
{
    /**
     * Whether a return for the given reason is still allowed for a shipment sent at $shippedAt
     * when the reason's deadline is extended by $graceDays.
     */
    public function isWithinDeadlineWithGrace(
        OrderReturnReasonInterface $reason,
        \DateTimeInterface $shippedAt,
        int $graceDays,
    ): bool;
}
