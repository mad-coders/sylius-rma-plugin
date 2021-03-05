<?php
/*
 * This file is part of the Madcoders RMA Plugin.
 *
 * (c) Leonid Moshko
 *
 */
declare(strict_types=1);

namespace Madcoders\SyliusRmaPlugin\Entity;

use Sylius\Component\Resource\Model\ResourceInterface as ResourceInterface;

class OrderReturnItem implements OrderReturnItemInterface, ResourceInterface
{
    /**
     * @var bool
     */
    private $itemToReturn = false;

    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $maxQty = 0;

    /**
     * @var OrderReturn
     */
    private $orderReturn;

    /**
     * @var string
     */
    private $productSku;

    /**
     * @var string|null
     */
    private $productName;

    /**
     * @var int
     */
    private $returnQty = 0;

    /**
     * @var int
     */
    private $unitPrice;

    /**
     * @return bool
     */
    public function isItemToReturn(): bool
    {
        return $this->itemToReturn;
    }

    /**
     * @param bool $itemToReturn
     */
    public function setItemToReturn(bool $itemToReturn): void
    {
        $this->itemToReturn = $itemToReturn;
    }

    /**
     * @return int
     */
    public function getMaxQty(): int
    {
        return $this->maxQty;
    }

    /**
     * @param int $maxQty
     */
    public function setMaxQty(int $maxQty): void
    {
        $this->maxQty = $maxQty;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return OrderReturn
     */
    public function getOrderReturn(): OrderReturn
    {
        return $this->orderReturn;
    }

    /**
     * @param OrderReturn $orderReturn
     */
    public function setOrderReturn(OrderReturn $orderReturn): void
    {
        $this->orderReturn = $orderReturn;
    }

    /**
     * @return string
     */
    public function getProductSku(): string
    {
        return $this->productSku;
    }

    /**
     * @param string $productSku
     */
    public function setProductSku(string $productSku): void
    {
        $this->productSku = $productSku;
    }

    /**
     * @return string|null
     */
    public function getProductName(): ?string
    {
        return $this->productName;
    }

    /**
     * @param string|null $productName
     */
    public function setProductName(?string $productName): void
    {
        $this->productName = $productName;
    }

    /**
     * @return int
     */
    public function getReturnQty(): int
    {
        return $this->returnQty;
    }

    /**
     * @param int $returnQty
     */
    public function setReturnQty(int $returnQty): void
    {
        $this->returnQty = $returnQty;
    }

    /**
     * @return int
     */
    public function getUnitPrice(): int
    {
        return $this->unitPrice;
    }

    /**
     * @param int $unitPrice
     */
    public function setUnitPrice(int $unitPrice): void
    {
        $this->unitPrice = $unitPrice;
    }
}
