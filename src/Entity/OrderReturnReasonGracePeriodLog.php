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

class OrderReturnReasonGracePeriodLog implements OrderReturnReasonGracePeriodLogInterface
{
    use TimestampableTrait;

    /** @var int|null */
    private $id;

    private OrderInterface $order;

    private string $reasonCode;

    private string $action;

    private ?int $previousExtraDays = null;

    private ?int $extraDays = null;

    private ?string $note = null;

    private OrderReturnChangeLogAuthor $author;

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

    public function getReasonCode(): string
    {
        return $this->reasonCode;
    }

    public function setReasonCode(string $reasonCode): void
    {
        $this->reasonCode = $reasonCode;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function setAction(string $action): void
    {
        $this->action = $action;
    }

    public function getPreviousExtraDays(): ?int
    {
        return $this->previousExtraDays;
    }

    public function setPreviousExtraDays(?int $previousExtraDays): void
    {
        $this->previousExtraDays = $previousExtraDays;
    }

    public function getExtraDays(): ?int
    {
        return $this->extraDays;
    }

    public function setExtraDays(?int $extraDays): void
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

    public function getAuthor(): OrderReturnChangeLogAuthor
    {
        return $this->author;
    }

    public function setAuthor(OrderReturnChangeLogAuthor $author): void
    {
        $this->author = $author;
    }
}
