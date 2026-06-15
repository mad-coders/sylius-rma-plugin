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

use Doctrine\ORM\Mapping as ORM;

/**
 * Supplies the per-product "non-returnable" flag (column + accessors) to an application's Sylius
 * Product model. Use it together with {@see NonReturnableProductInterface}:
 *
 *     class Product extends BaseProduct implements NonReturnableProductInterface
 *     {
 *         use NonReturnableProductTrait;
 *     }
 *
 * The column is mapped here, so an application only has to use the trait (the plugin migration adds
 * the `non_returnable` column to `sylius_product`). It defaults to false, so applying the trait does
 * not change behaviour until a product is explicitly flagged.
 */
trait NonReturnableProductTrait
{
    #[ORM\Column(name: 'non_returnable', type: 'boolean', options: ['default' => false])]
    protected bool $nonReturnable = false;

    public function isNonReturnable(): bool
    {
        return $this->nonReturnable;
    }

    public function setNonReturnable(bool $nonReturnable): void
    {
        $this->nonReturnable = $nonReturnable;
    }
}
