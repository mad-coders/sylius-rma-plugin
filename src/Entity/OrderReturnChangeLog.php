<?php
/*
 * This file is part of the Madcoders RMA Plugin.
 *
 * (c) Leonid Moshko
 *
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
}
