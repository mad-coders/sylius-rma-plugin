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

namespace Madcoders\SyliusRmaPlugin\Services;

use Madcoders\SyliusRmaPlugin\Services\Reason\ChoiceProvider;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Exception;

class RmaVerificationPossibilityOfReturn
{
    /** @var MaxQtyCalculator */
    private $maxQtyCalculator;

    /** @var ChoiceProvider */
    private $availableReasonsCreator;

    /**
     * RmaVerificationPossibilityOfReturn constructor.
     * @param MaxQtyCalculator $maxQtyCalculator
     * @param ChoiceProvider $availableReasonsCreator
     */
    public function __construct(
        MaxQtyCalculator $maxQtyCalculator,
        ChoiceProvider $availableReasonsCreator
    )
    {
        $this->maxQtyCalculator = $maxQtyCalculator;
        $this->availableReasonsCreator = $availableReasonsCreator;
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

        if (count($this->availableReasonsCreator->createAvailableReasons($order)) < 1) {
            return false;
        }

        if ($orderQty > 0) {
            return true;
        }

        return false;
    }
}
