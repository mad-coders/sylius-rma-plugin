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
    public function getId(): ?int;

    public function getType(): ?string;

    public function setType(string $type): void;

    public function getNote(): ?string;

    public function setNote(string $note): void;

    public function getReturnNumber(): string;

    public function setReturnNumber(string $returnNumber): void;

    public function getAuthor(): OrderReturnChangeLogAuthor;

    public function setAuthor(OrderReturnChangeLogAuthor $author): void;
}
