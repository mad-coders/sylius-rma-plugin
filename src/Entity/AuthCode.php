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

class AuthCode implements ResourceInterface, AuthCodeInterface
{
    public const DEFAULT_MAX_ATTEMPTS = 3;

    /** @var int */
    private $id;

    /** @var string */
    private $orderNumber;

    /** @var string */
    private $hash;

    /** @var int */
    private $authCode;

    /** @var int */
    private $attempts = 0;

    /** @var \DateTimeInterface|null */
    private $expiresAt;

    public function __construct()
    {
        $this->expiresAt = new \DateTime();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getOrderNumber(): string
    {
        return $this->orderNumber;
    }

    /**
     * @param string $orderNumber
     */
    public function setOrderNumber(string $orderNumber): void
    {
        $this->orderNumber = $orderNumber;
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * @param string $hash
     */
    public function setHash(string $hash): void
    {
        $this->hash = $hash;
    }

    /**
     * @return int
     */
    public function getAuthCode(): int
    {
        return $this->authCode;
    }

    /**
     * @param int $authCode
     */
    public function setAuthCode(int $authCode): void
    {
        $this->authCode = $authCode;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getExpiresAt(): \DateTimeInterface
    {
        return $this->expiresAt;
    }

    /**
     * @param \DateTimeInterface $expiresAt
     */
    public function setExpiresAt(\DateTimeInterface $expiresAt): void
    {
        $this->expiresAt = $expiresAt;
    }

    /**
     * @return int
     */
    public function getAttempts(): int
    {
        return $this->attempts;
    }

    /**
     * @param int $attempts
     */
    public function setAttempts(int $attempts): void
    {
        $this->attempts = $attempts;
    }

    public function increaseNumberOfAttempts(): void
    {
        $this->attempts++;
    }
}
