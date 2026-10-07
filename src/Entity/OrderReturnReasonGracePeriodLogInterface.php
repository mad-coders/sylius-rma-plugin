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

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\TimestampableInterface;

/**
 * Audit entry for a grant, change or revocation of an order's grace period (#67).
 *
 * Stores the reason by code rather than by reference, so the history stays readable after
 * the grace period (or the reason itself) is deleted.
 */
interface OrderReturnReasonGracePeriodLogInterface extends ResourceInterface, TimestampableInterface
{
    public const ACTION_GRANTED = 'granted';

    public const ACTION_UPDATED = 'updated';

    public const ACTION_REVOKED = 'revoked';

    public function getId(): ?int;

    public function getOrder(): OrderInterface;

    public function setOrder(OrderInterface $order): void;

    public function getReasonCode(): string;

    public function setReasonCode(string $reasonCode): void;

    public function getAction(): string;

    public function setAction(string $action): void;

    public function getPreviousExtraDays(): ?int;

    public function setPreviousExtraDays(?int $previousExtraDays): void;

    public function getExtraDays(): ?int;

    public function setExtraDays(?int $extraDays): void;

    public function getNote(): ?string;

    public function setNote(?string $note): void;

    public function getAuthor(): OrderReturnChangeLogAuthor;

    public function setAuthor(OrderReturnChangeLogAuthor $author): void;
}
