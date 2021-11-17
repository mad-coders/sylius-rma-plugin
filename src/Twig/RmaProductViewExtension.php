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

namespace Madcoders\SyliusRmaPlugin\Twig;

use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Repository\ProductVariantRepositoryInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class RmaProductViewExtension extends AbstractExtension
{
    /** @var ProductVariantRepositoryInterface */
    private $productVariantRepository;

    /**
     * RmaProductViewExtension constructor.
     * @param ProductVariantRepositoryInterface $productVariantRepository
     */
    public function __construct(ProductVariantRepositoryInterface $productVariantRepository)
    {
        $this->productVariantRepository = $productVariantRepository;
    }

    /** {@inheritdoc} */
    public function getFunctions()
    {
        return [
            new TwigFunction('rma_product_view', [$this, 'findProductByVariantCode']),
        ];
    }

    public function findProductByVariantCode( string $productSku = null): ?ProductVariantInterface
    {
        $productVariant = $this->productVariantRepository->findOneBy(array('code' => $productSku));
        if (!$productVariant instanceof ProductVariantInterface) {
            return null;
        }

//        $product = $productVariant->getProduct();
//        if (!$product instanceof ProductInterface) {
//            return null;
//        }

        return $productVariant;
    }
}
