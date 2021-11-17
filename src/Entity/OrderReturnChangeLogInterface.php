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

interface OrderReturnChangeLogInterface
{
    /** @return int */
    public function getId(): ?int;

    /** @return ?string */
    public function getType(): ?string;

    /** @param string $type */
    public function setType(string $type): void;

    /** @return ?string */
    public function getNote(): ?string;

    /** @param string $note */
    public function setNote(string $note): void;

    /** @return string */
    public function getReturnNumber(): string;

    /** @param string $returnNumber */
    public function setReturnNumber(string $returnNumber): void;

    /** @return OrderReturnChangeLogAuthor */
    public function getAuthor(): OrderReturnChangeLogAuthor;

    /** @param OrderReturnChangeLogAuthor $author */
    public function setAuthor(OrderReturnChangeLogAuthor $author): void;
}
