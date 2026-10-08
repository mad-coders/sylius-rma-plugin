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

namespace Madcoders\SyliusRmaPlugin\Entity;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\TimestampableInterface;

/**
 * Extra days granted on top of a return reason's deadline for one specific order (#67).
 * At most one grace period exists per order and reason.
 */
interface OrderReturnReasonGracePeriodInterface extends ResourceInterface, TimestampableInterface
{
    public const MIN_EXTRA_DAYS = 1;

    public const MAX_EXTRA_DAYS = 365;

    public function getId(): ?int;

    public function getOrder(): OrderInterface;

    public function setOrder(OrderInterface $order): void;

    public function getReason(): OrderReturnReasonInterface;

    public function setReason(OrderReturnReasonInterface $reason): void;

    public function getExtraDays(): int;

    public function setExtraDays(int $extraDays): void;

    public function getNote(): ?string;

    public function setNote(?string $note): void;
}
