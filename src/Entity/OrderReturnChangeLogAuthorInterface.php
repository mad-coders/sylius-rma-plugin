<?php
/*
 * This file is part of the Madcoders RMA Plugin.
 *
 * (c) Leonid Moshko
 *
 */
declare(strict_types=1);

namespace Madcoders\SyliusRmaPlugin\Entity;

interface OrderReturnChangeLogAuthorInterface
{
    /** @return int */
    public function getId(): int;

    /** @param int $id */
    public function setId(int $id): void;

    /** @return string */
    public function getType(): string;

    /** @param string $type */
    public function setType(string $type): void;

    /**  @return string */
    public function getFirstName(): string;

    /** @param string $firstName */
    public function setFirstName(string $firstName): void;

    /** @return string */
    public function getLastName(): string;

    /** @param string $lastName */
    public function setLastName(string $lastName): void;

    /** @return OrderReturnChangeLog */
    public function getChangeLog(): OrderReturnChangeLog;

    /** @param OrderReturnChangeLog $changeLog */
    public function setChangeLog(OrderReturnChangeLog $changeLog): void;
}
