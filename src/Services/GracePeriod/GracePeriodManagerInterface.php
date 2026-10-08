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

namespace Madcoders\SyliusRmaPlugin\Services\GracePeriod;

use Madcoders\SyliusRmaPlugin\Entity\OrderReturnChangeLogAuthor;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnReasonGracePeriodInterface;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnReasonGracePeriodLogInterface;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnReasonInterface;
use Sylius\Component\Core\Model\OrderInterface;

interface GracePeriodManagerInterface
{
    /**
     * Grants extra days on the reason for this order, or changes the existing grant (one per
     * order and reason). Returns the audit entry written, or null when nothing changed.
     */
    public function grant(
        OrderInterface $order,
        OrderReturnReasonInterface $reason,
        int $extraDays,
        ?string $note,
        OrderReturnChangeLogAuthor $author,
    ): ?OrderReturnReasonGracePeriodLogInterface;

    /**
     * Removes the grace period; the order falls back to the reason's base deadline.
     */
    public function revoke(
        OrderReturnReasonGracePeriodInterface $gracePeriod,
        OrderReturnChangeLogAuthor $author,
    ): OrderReturnReasonGracePeriodLogInterface;
}
