<?php
/*
 * This file is part of the Madcoders RMA Plugin.
 *
 * (c) Leonid Moshko
 *
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
}
