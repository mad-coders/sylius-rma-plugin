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

use Sylius\Component\Resource\Model\ResourceInterface as ResourceInterface;
use Sylius\Component\Resource\Model\TimestampableInterface;
use Sylius\Component\Resource\Model\TimestampableTrait;

class OrderReturnChangeLog implements OrderReturnChangeLogInterface, ResourceInterface, TimestampableInterface
{
    use TimestampableTrait;

    /** @var int */
    private $id;

    private string $type;

    private ?string $note = null;

    private string $returnNumber;

    private OrderReturnChangeLogAuthor $author;

    public function getId(): int
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getNote(): string
    {
        return $this->note ?? '';
    }

    public function setNote(string $note): void
    {
        $this->note = $note;
    }

    public function getReturnNumber(): string
    {
        return $this->returnNumber;
    }

    public function setReturnNumber(string $returnNumber): void
    {
        $this->returnNumber = $returnNumber;
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
