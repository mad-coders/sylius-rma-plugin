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
    public function getId(): ?int;

    public function isItemToReturn(): bool;

    public function setItemToReturn(bool $itemToReturn): void;

    public function getMaxQty(): int;

    public function setMaxQty(int $maxQty): void;

    public function getOrderReturn(): OrderReturn;

    public function setOrderReturn(OrderReturn $orderReturn): void;

    public function getProductSku(): string;

    public function setProductSku(string $productSku): void;

    public function getProductName(): ?string;

    public function setProductName(?string $productName): void;

    public function getReturnQty(): int;

    public function setReturnQty(int $returnQty): void;

    public function getUnitPrice(): int;

    public function setUnitPrice(int $unitPrice): void;
}
