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

class OrderReturnItem implements OrderReturnItemInterface, ResourceInterface
{
    private bool $itemToReturn = false;

    /** @var int */
    private $id;

    private int $maxQty = 0;

    private OrderReturn $orderReturn;

    private string $productSku;

    private ?string $productName = null;

    private int $returnQty = 0;

    private int $unitPrice = 0;

    public function isItemToReturn(): bool
    {
        return $this->itemToReturn;
    }

    public function setItemToReturn(bool $itemToReturn): void
    {
        $this->itemToReturn = $itemToReturn;
    }

    public function getMaxQty(): int
    {
        return $this->maxQty;
    }

    public function setMaxQty(int $maxQty): void
    {
        $this->maxQty = $maxQty;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getOrderReturn(): OrderReturn
    {
        return $this->orderReturn;
    }

    public function setOrderReturn(OrderReturn $orderReturn): void
    {
        $this->orderReturn = $orderReturn;
    }

    public function getProductSku(): string
    {
        return $this->productSku;
    }

    public function setProductSku(string $productSku): void
    {
        $this->productSku = $productSku;
    }

    public function getProductName(): ?string
    {
        return $this->productName;
    }

    public function setProductName(?string $productName): void
    {
        $this->productName = $productName;
    }

    public function getReturnQty(): int
    {
        return $this->returnQty;
    }

    public function setReturnQty(int $returnQty): void
    {
        $this->returnQty = $returnQty;
    }

    public function getUnitPrice(): int
    {
        return $this->unitPrice;
    }

    public function setUnitPrice(int $unitPrice): void
    {
        $this->unitPrice = $unitPrice;
    }
}
