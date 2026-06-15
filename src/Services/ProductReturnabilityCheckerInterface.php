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

use Sylius\Component\Core\Model\ProductVariantInterface;

interface ProductReturnabilityCheckerInterface
{
    /**
     * Tells whether a single ordered variant may be added to a return request.
     *
     * This is an item-level decision layered on top of the order-level
     * {@see ReturnEligibilityCheckerInterface}: even when an order is returnable, individual
     * variants (perishables, hygiene/sealed goods, made-to-order items, gift cards, ...) can be
     * excluded from the return flow. A non-returnable variant is never offered for return and is
     * never persisted onto an OrderReturn.
     */
    public function isReturnable(ProductVariantInterface $variant): bool;
}
