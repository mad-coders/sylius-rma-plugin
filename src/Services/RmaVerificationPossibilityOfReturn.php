<?php
/*
 * This file is part of the Madcoders RMA Plugin.
 *
 * (c) Leonid Moshko
 *
 */
declare(strict_types=1);

namespace Madcoders\SyliusRmaPlugin\Services;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Exception;

class RmaVerificationPossibilityOfReturn
{
    /** @var MaxQtyCalculator */
    private $maxQtyCalculator;

    /**
     * RmaVerificationPossibilityOfReturn constructor.
     * @param MaxQtyCalculator $maxQtyCalculator
     */
    public function __construct(MaxQtyCalculator $maxQtyCalculator)
    {
        $this->maxQtyCalculator = $maxQtyCalculator;
    }

    /**
     * @param OrderInterface $order
     * @return bool
     * @throws Exception
     */
    public function verificationForButtonRender(OrderInterface $order): bool
    {
        if (!$orderNumber = $order->getNumber()) {
            throw new Exception('Order number not find');
        }

        $orderItems = $order->getItems();
        $orderQty = 0;

        /** @var OrderItemInterface $items */
        foreach ($orderItems as $item) {

            $originalQty = $item->getQuantity();
            if (!$itemVariant = $item->getVariant()) {
                throw new Exception('itemVariant not find');
            }

            $itemVariantCode = $itemVariant->getCode();
            $orderQty = $orderQty + $this->maxQtyCalculator->calculation($orderNumber, $itemVariantCode, $originalQty);
        }

        if ($orderQty > 0) {
            return true;
        }

        return false;
    }
}
