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

namespace Madcoders\SyliusRmaPlugin\Entity;

/**
 * Implemented by the application's Sylius Product to carry the per-product "non-returnable" flag.
 *
 * Apply {@see NonReturnableProductTrait} on the application Product model to satisfy this contract
 * (it supplies the `non_returnable` column and accessors; the plugin migration adds the column to
 * `sylius_product`). The default
 * {@see \Madcoders\SyliusRmaPlugin\Services\ProductReturnabilityChecker} reads the flag through this
 * interface; a product whose model does not implement it is always returnable.
 */
interface NonReturnableProductInterface
{
    public function isNonReturnable(): bool;

    public function setNonReturnable(bool $nonReturnable): void;
}
