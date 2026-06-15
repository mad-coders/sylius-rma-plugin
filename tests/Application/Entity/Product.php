<?php

/*
 * This file is part of package:
 * Sylius RMA Plugin
 *
 * @copyright MADCODERS Team (www.madcoders.co)
 * @licence For the full copyright and license information, please view the LICENSE
 */

declare(strict_types=1);

namespace Tests\Madcoders\SyliusRmaPlugin\Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Madcoders\SyliusRmaPlugin\Entity\NonReturnableProductInterface;
use Sylius\Component\Core\Model\Product as BaseProduct;

/**
 * Test-application Product overriding the Sylius core model so the plugin's per-product
 * "non-returnable" flag can be exercised end to end (admin checkbox + return flow + Behat).
 */
#[ORM\Entity]
#[ORM\Table(name: 'sylius_product')]
class Product extends BaseProduct implements NonReturnableProductInterface
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
