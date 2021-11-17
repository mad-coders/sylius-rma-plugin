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

    /** @var string */
    private $type;

    /** @var string */
    private $note;

    /** @var string */
    private $returnNumber;

    /** @var OrderReturnChangeLogAuthor */
    private $author;

    /** @return int */
    public function getId(): int
    {
        return $this->id;
    }

    /** @return string */
    public function getType(): string
    {
        return $this->type;
    }

    /** @param string $type */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /** @return string */
    public function getNote(): string
    {
        return $this->note;
    }

    /** @param string $note */
    public function setNote(string $note): void
    {
        $this->note = $note;
    }

    /** @return string */
    public function getReturnNumber(): string
    {
        return $this->returnNumber;
    }

    /** @param string $returnNumber */
    public function setReturnNumber(string $returnNumber): void
    {
        $this->returnNumber = $returnNumber;
    }

    /** @return OrderReturnChangeLogAuthor */
    public function getAuthor(): OrderReturnChangeLogAuthor
    {
        return $this->author;
    }

    /** @param OrderReturnChangeLogAuthor $author */
    public function setAuthor(OrderReturnChangeLogAuthor $author): void
    {
        $this->author = $author;
    }
}
