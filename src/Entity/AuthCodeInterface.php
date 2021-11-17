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
    /**
     * @return int|null
     */
    public function getId(): ?int;

    /**
     * @return string
     */
    public function getOrderNumber(): string;

    /**
     * @param string $orderNumber
     */
    public function setOrderNumber(string $orderNumber): void;

    /**
     * @return string
     */
    public function getHash(): string;

    /**
     * @param string $hash
     */
    public function setHash(string $hash): void;

    /**
     * @return int
     */
    public function getAuthCode(): int;

    /**
     * @param int $authCode
     */
    public function setAuthCode(int $authCode): void;

    /**
     * @return \DateTimeInterface
     */
    public function getExpiresAt(): \DateTimeInterface;

    /**
     * @param \DateTimeInterface $expiresAt
     */
    public function setExpiresAt(\DateTimeInterface $expiresAt): void;


    /**
     * @return int
     */
    public function getAttempts(): int;

    /**
     * @param int $attempts
     */
    public function setAttempts(int $attempts): void;

    public function increaseNumberOfAttempts(): void;
}
