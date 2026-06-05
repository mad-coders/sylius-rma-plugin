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

interface AuthCodeInterface
{
    public function getId(): ?int;

    public function getOrderNumber(): string;

    public function setOrderNumber(string $orderNumber): void;

    public function getHash(): string;

    public function setHash(string $hash): void;

    public function getAuthCode(): int;

    public function setAuthCode(int $authCode): void;

    public function getExpiresAt(): \DateTimeInterface;

    public function setExpiresAt(\DateTimeInterface $expiresAt): void;

    public function getAttempts(): int;

    public function setAttempts(int $attempts): void;

    public function increaseNumberOfAttempts(): void;
}
