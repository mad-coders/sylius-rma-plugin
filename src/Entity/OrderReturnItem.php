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

class OrderReturnItem implements ResourceInterface
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var OrderReturn
     */
    private $orderReturn;

    /**
     * @var string
     */
    private $productName;

    /**
     * @var int
     */
    private $returnQty;

    /**
     * @var int
     */
    private $unitPrice;

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
    public function getProductName(): string
    {
        return $this->productName;
    }

    /**
     * @param string $productName
     */
    public function setProductName(string $productName): void
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
