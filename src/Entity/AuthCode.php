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

    private string $orderNumber;

    private string $hash;

    private int $authCode = 0;

    private int $attempts = 0;

    private \DateTime|\DateTimeInterface $expiresAt;

    public function __construct()
    {
        $this->expiresAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderNumber(): string
    {
        return $this->orderNumber;
    }

    public function setOrderNumber(string $orderNumber): void
    {
        $this->orderNumber = $orderNumber;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function setHash(string $hash): void
    {
        $this->hash = $hash;
    }

    public function getAuthCode(): int
    {
        return $this->authCode;
    }

    public function setAuthCode(int $authCode): void
    {
        $this->authCode = $authCode;
    }

    public function getExpiresAt(): \DateTimeInterface
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(\DateTimeInterface $expiresAt): void
    {
        $this->expiresAt = $expiresAt;
    }

    public function getAttempts(): int
    {
        return $this->attempts;
    }

    public function setAttempts(int $attempts): void
    {
        $this->attempts = $attempts;
    }

    public function increaseNumberOfAttempts(): void
    {
        ++$this->attempts;
    }
}
