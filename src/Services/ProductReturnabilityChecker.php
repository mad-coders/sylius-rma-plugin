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

use Madcoders\SyliusRmaPlugin\Entity\NonReturnableProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;

/**
 * Default {@see ProductReturnabilityCheckerInterface}: a variant is non-returnable when its product
 * is flagged via the admin "non-returnable" checkbox (the {@see NonReturnableProductInterface} flag).
 *
 * When the application Product model does not implement that interface, or the flag is off, the
 * variant is returnable - so behaviour is unchanged until a product is explicitly flagged.
 */
final readonly class ProductReturnabilityChecker implements ProductReturnabilityCheckerInterface
{
    public function isReturnable(ProductVariantInterface $variant): bool
    {
        $product = $variant->getProduct();

        if ($product instanceof NonReturnableProductInterface) {
            return !$product->isNonReturnable();
        }

        return true;
    }
}
