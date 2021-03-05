<?php
/*
 * This file is part of the Madcoders RMA Plugin.
 *
 * (c) Leonid Moshko
 *
 */
declare(strict_types=1);

namespace Madcoders\SyliusRmaPlugin\Entity;

class OrderItemReturnRequest
{
    /**
     * @var bool
     */
    private $itemToReturn = false;

    /**
     * @var int
     */
    private $maxQty = 0;

    /**
     * @var int
     */
    private $returnQty = 0;

    /**
     * @var string
     */
    private $productSku;

    /**
     * @var string|null
     */
    private $productName;

    public function __construct(
        ?string $productName,
        string $productSku,
        int $maxQty,
        int $returnQty,
        bool $itemToReturn = false)
    {
        $this->productName = $productName;
        $this->productSku = $productSku;
        $this->maxQty = $maxQty;
        $this->returnQty = $returnQty;
        $this->itemToReturn = $itemToReturn;
    }

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
     * @return ?string
     */
    public function getProductName(): ?string
    {
        return $this->productName;
    }

    /**
     * @param ?string $productName
     */
    public function setProductName(?string $productName): void
    {
        $this->productName = $productName;
    }




}
