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

interface OrderReturnItemInterface
{
    /** @return int */
    public function getId(): ?int;

    /** @return bool */
    public function isItemToReturn(): bool;

    /** @param bool $itemToReturn */
    public function setItemToReturn(bool $itemToReturn): void;

    /** @return int */
    public function getMaxQty(): int;

    /** @param int $maxQty */
    public function setMaxQty(int $maxQty): void;

    /** @return OrderReturn */
    public function getOrderReturn(): OrderReturn;

    /** @param OrderReturn $orderReturn */
    public function setOrderReturn(OrderReturn $orderReturn): void;

    /** @return string */
    public function getProductSku(): string;

    /** @param string $productSku */
    public function setProductSku(string $productSku): void;

    /** @return ?string */
    public function getProductName(): ?string;

    /** @param ?string $productName */
    public function setProductName(?string $productName): void;

    /** @return int */
    public function getReturnQty(): int;

    /** @param int $returnQty */
    public function setReturnQty(int $returnQty): void;

    /** @return int */
    public function getUnitPrice(): int;

    /** @param int $unitPrice */
    public function setUnitPrice(int $unitPrice): void;
}
