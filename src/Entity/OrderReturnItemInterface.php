<?php
/*
 * This file is part of the Madcoders RMA Plugin.
 *
 * (c) Leonid Moshko
 *
 */
declare(strict_types=1);

namespace Madcoders\SyliusRmaPlugin\Entity;

interface OrderReturnItemInterface
{
    /**
     * @return int
     */
    public function getId(): ?int;

    /**
     * @return OrderReturn
     */
    public function getOrderReturn(): OrderReturn;

    /**
     * @param OrderReturn $orderReturn
     */
    public function setOrderReturn(OrderReturn $orderReturn): void;

    /**
     * @return string
     */
    public function getProductName(): string;

    /**
     * @param string $productName
     */
    public function setProductName(string $productName): void;

    /**
     * @return int
     */
    public function getReturnQty(): int;
    /**
     * @param int $returnQty
     */
    public function setReturnQty(int $returnQty): void;

    /**
     * @return int
     */
    public function getUnitPrice(): int;

    /**
     * @param int $unitPrice
     */
    public function setUnitPrice(int $unitPrice): void;
}