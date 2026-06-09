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

namespace Madcoders\SyliusRmaPlugin\Fixture\Factory;

use Madcoders\SyliusRmaPlugin\Entity\OrderReturn;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnItem;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnItemInterface;
use Madcoders\SyliusRmaPlugin\Repository\OrderReturnRepository;
use Sylius\Bundle\CoreBundle\Fixture\Factory\AbstractExampleFactory;
use Sylius\Bundle\CoreBundle\Fixture\Factory\ExampleFactoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Webmozart\Assert\Assert;

final class OrderReturnItemFixtureFactory extends AbstractExampleFactory implements ExampleFactoryInterface
{
    private readonly OptionsResolver $optionsResolver;

    public function __construct(private readonly OrderReturnRepository $orderReturnRepository)
    {
        $this->optionsResolver = new OptionsResolver();

        $this->configureOptions($this->optionsResolver);
    }

    /**
     * @inheritdoc
     */
    public function create(array $options = []): OrderReturnItemInterface
    {
        $options = $this->optionsResolver->resolve($options);

        Assert::string($options['return_number']);
        Assert::string($options['product_sku']);
        Assert::string($options['product_name']);
        Assert::integer($options['return_qty']);
        Assert::integer($options['unit_price']);

        $orderReturn = $this->orderReturnRepository->findOneBy(['returnNumber' => $options['return_number']]);
        if (!$orderReturn instanceof OrderReturn) {
            throw new \Exception(sprintf('Return %s has not been found, please create it before adding this fixture!', $options['return_number']));
        }

        $orderReturnItem = new OrderReturnItem();
        $orderReturnItem->setOrderReturn($orderReturn);
        $orderReturnItem->setProductSku($options['product_sku']);
        $orderReturnItem->setProductName($options['product_name']);
        $orderReturnItem->setReturnQty($options['return_qty']);
        $orderReturnItem->setUnitPrice($options['unit_price']);

        return $orderReturnItem;
    }

    /**
     * @inheritdoc
     */
    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('return_number')
            ->setAllowedTypes('return_number', 'string')

            ->setRequired('product_sku')
            ->setAllowedTypes('product_sku', 'string')

            ->setRequired('product_name')
            ->setAllowedTypes('product_name', 'string')

            ->setRequired('return_qty')
            ->setAllowedTypes('return_qty', 'integer')

            ->setRequired('unit_price')
            ->setAllowedTypes('unit_price', 'integer')
        ;
    }
}
