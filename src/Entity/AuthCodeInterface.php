<?php
/*
 * This file is part of the Madcoders RMA Plugin.
 *
 * (c) Piotr Lewandowski
 *
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
