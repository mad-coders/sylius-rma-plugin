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
 * Allows a return while the total number of days elapsed since shipment does not
 * exceed the reason's deadline.
 *
 * Uses DateInterval::$days (total elapsed days), not DateInterval::$d (the
 * day-of-month component), so the deadline keeps being enforced even when more
 * than a calendar month has passed since shipment.
 */
final class ElapsedDaysReturnDeadlineChecker implements ReturnDeadlineCheckerInterface
{
    public function isWithinDeadline(OrderReturnReasonInterface $reason, \DateTimeInterface $shippedAt): bool
    {
        $deadlineToReturn = $reason->getDeadlineToReturn();
        if (null === $deadlineToReturn) {
            return false;
        }

        $elapsedDays = $shippedAt->diff(new \DateTimeImmutable())->days;

        return $deadlineToReturn >= $elapsedDays;
    }
}
