<?php
/*
 * This file is part of the Madcoders RMA Plugin.
 *
 * (c) Leonid Moshko
 *
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
