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

use Madcoders\SyliusRmaPlugin\Entity\OrderReturnReasonGracePeriodInterface;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnReasonInterface;

/**
 * One reason as shown in the admin order grace period panel.
 */
final readonly class GracePeriodOverviewRow
{
    public function __construct(
        public OrderReturnReasonInterface $reason,
        public int $baseDays,
        public ?OrderReturnReasonGracePeriodInterface $gracePeriod,
        public ?\DateTimeImmutable $lastReturnDay,
    ) {
    }

    public function getExtraDays(): ?int
    {
        return $this->gracePeriod?->getExtraDays();
    }
}
