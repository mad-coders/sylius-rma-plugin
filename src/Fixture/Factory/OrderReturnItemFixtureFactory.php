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

use Madcoders\SyliusRmaPlugin\Entity\OrderReturnInterface;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnItem;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnItemInterface;
use Sylius\Bundle\CoreBundle\Fixture\Factory\AbstractExampleFactory;
use Sylius\Bundle\CoreBundle\Fixture\Factory\ExampleFactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class OrderReturnItemFixtureFactory extends AbstractExampleFactory implements ExampleFactoryInterface
{
    /** @var OptionsResolver */
    private $optionsResolver;

    /** @var \Faker\Generator */
    private $faker;

    /** @var RepositoryInterface */
    private $orderReturnRepository;

    public function __construct(RepositoryInterface $orderReturnRepository)
    {
        $this->faker = \Faker\Factory::create();
        $this->optionsResolver = new OptionsResolver();

        $this->configureOptions($this->optionsResolver);
        $this->orderReturnRepository = $orderReturnRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $options = []): OrderReturnItemInterface
    {
        $options = $this->optionsResolver->resolve($options);

        $orderReturn = $this->orderReturnRepository->findOneByReturnNumber($options['return_number']);
        if (!$orderReturn instanceof OrderReturnInterface) {
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
     * {@inheritdoc}
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
