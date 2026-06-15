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

namespace Tests\Madcoders\SyliusRmaPlugin\Unit\Services;

use Madcoders\SyliusRmaPlugin\Entity\NonReturnableProductInterface;
use Madcoders\SyliusRmaPlugin\Services\ProductReturnabilityChecker;
use Prophecy\PhpUnit\ProphecyTrait;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Tests\Madcoders\SyliusRmaPlugin\Unit\UnitTestCase;

class ProductReturnabilityCheckerTest extends UnitTestCase
{
    use ProphecyTrait;

    /** @test */
    public function a_variant_of_a_flagged_product_is_not_returnable()
    {
        $variant = $this->variantWithProduct($this->nonReturnableProduct(true));

        $this->assertFalse((new ProductReturnabilityChecker())->isReturnable($variant));
    }

    /** @test */
    public function a_variant_of_an_unflagged_product_is_returnable()
    {
        $variant = $this->variantWithProduct($this->nonReturnableProduct(false));

        $this->assertTrue((new ProductReturnabilityChecker())->isReturnable($variant));
    }

    /** @test */
    public function a_variant_whose_product_does_not_support_the_flag_is_returnable()
    {
        // a plain Sylius product (model not implementing the interface) is always returnable
        $product = $this->prophesize(ProductInterface::class);
        $variant = $this->variantWithProduct($product->reveal());

        $this->assertTrue((new ProductReturnabilityChecker())->isReturnable($variant));
    }

    /** @test */
    public function a_variant_without_a_product_is_returnable()
    {
        $variant = $this->variantWithProduct(null);

        $this->assertTrue((new ProductReturnabilityChecker())->isReturnable($variant));
    }

    private function variantWithProduct(?ProductInterface $product): ProductVariantInterface
    {
        $variant = $this->prophesize(ProductVariantInterface::class);
        $variant->getProduct()->willReturn($product);

        return $variant->reveal();
    }

    private function nonReturnableProduct(bool $nonReturnable): ProductInterface
    {
        $product = $this->prophesize(ProductInterface::class);
        $product->willImplement(NonReturnableProductInterface::class);
        $product->isNonReturnable()->willReturn($nonReturnable);

        return $product->reveal();
    }
}
