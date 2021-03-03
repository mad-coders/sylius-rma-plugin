<?php

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
     * @return \DateTimeInterface|null
     */
    public function getExpiresAt(): ?\DateTimeInterface;

    /**
     * @param \DateTimeInterface|null $expiresAt
     */
}
