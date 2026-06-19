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

use Exception;
use Madcoders\SyliusRmaPlugin\Services\Reason\ChoiceProvider;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;

class RmaVerificationPossibilityOfReturn
{
    /**
     * RmaVerificationPossibilityOfReturn constructor.
     */
    public function __construct(
        private readonly MaxQtyCalculator $maxQtyCalculator,
        private readonly ChoiceProvider $availableReasonsCreator,
        private readonly ProductReturnabilityCheckerInterface $productReturnabilityChecker,
    ) {
    }

    /**
     * @throws Exception
     */
    public function verificationForButtonRender(OrderInterface $order): bool
    {
        $orderNumber = $order->getNumber();
        if (null === $orderNumber) {
            throw new Exception('Order number not find');
        }

        $orderItems = $order->getItems();
        $orderQty = 0;

        /** @var OrderItemInterface $item */
        foreach ($orderItems as $item) {
            $originalQty = $item->getQuantity();
            $itemVariant = $item->getVariant();
            if (null === $itemVariant) {
                throw new Exception('itemVariant not find');
            }

            $itemVariantCode = $itemVariant->getCode();
            if (null === $itemVariantCode) {
                throw new Exception('itemVariant code not find');
            }

            // A non-returnable variant contributes no returnable quantity, so an order made up only of
            // non-returnable items reports "nothing to return" and the start-return button is hidden.
            if (!$this->productReturnabilityChecker->isReturnable($itemVariant)) {
                continue;
            }

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
