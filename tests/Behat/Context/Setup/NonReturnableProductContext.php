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

namespace Tests\Madcoders\SyliusRmaPlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Madcoders\SyliusRmaPlugin\Entity\NonReturnableProductInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Webmozart\Assert\Assert;

final class NonReturnableProductContext implements Context
{
    /**
     * @param RepositoryInterface<ProductInterface> $productRepository
     */
    public function __construct(private readonly RepositoryInterface $productRepository)
    {
    }

    /**
     * @Given /^the (product "[^"]+") is non-returnable$/
     */
    public function theProductIsNonReturnable(ProductInterface $product): void
    {
        Assert::isInstanceOf(
            $product,
            NonReturnableProductInterface::class,
            'The configured Product model must implement NonReturnableProductInterface for this scenario.',
        );

        $product->setNonReturnable(true);
        $this->productRepository->add($product);
    }
}
