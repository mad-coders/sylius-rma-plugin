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
use Sylius\Component\Resource\Model\TimestampableTrait;

class OrderReturnReasonGracePeriod implements OrderReturnReasonGracePeriodInterface
{
    use TimestampableTrait;

    /** @var int|null */
    private $id;

    private OrderInterface $order;

    private OrderReturnReasonInterface $reason;

    private int $extraDays = 0;

    private ?string $note = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrder(): OrderInterface
    {
        return $this->order;
    }

    public function setOrder(OrderInterface $order): void
    {
        $this->order = $order;
    }

    public function getReason(): OrderReturnReasonInterface
    {
        return $this->reason;
    }

    public function setReason(OrderReturnReasonInterface $reason): void
    {
        $this->reason = $reason;
    }

    public function getExtraDays(): int
    {
        return $this->extraDays;
    }

    public function setExtraDays(int $extraDays): void
    {
        $this->extraDays = $extraDays;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): void
    {
        $this->note = $note;
    }
}
