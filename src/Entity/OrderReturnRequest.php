<?php
/*
 * This file is part of the Madcoders RMA Plugin.
 *
 * (c) Leonid Moshko
 *
 */
declare(strict_types=1);

namespace Madcoders\SyliusRmaPlugin\Entity;

class OrderReturnRequest
{
    /**
     * @var OrderItemReturnRequest[]
     */
    private $items;

    /**
     * @var string|null
     */
    private $returnReason;

    public function __construct(array $items = [], ?string $returnReason = null)
    {
        $this->items = $items;
        $this->returnReason = $returnReason;
    }

    /**
     * @return OrderItemReturnRequest[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param OrderItemReturnRequest $item
     */
    public function addItem(OrderItemReturnRequest $item): void
    {
        $this->items[] = $item;
    }

    /**
     * @param OrderItemReturnRequest[] $items
     */
    public function setItems(array $items): void
    {
        $this->items = $items;
    }

    /**
     * @return string|null
     */
    public function getReturnReason(): ?string
    {
        return $this->returnReason;
    }

    /**
     * @param string|null $returnReason
     */
    public function setReturnReason(?string $returnReason): void
    {
        $this->returnReason = $returnReason;
    }
}
