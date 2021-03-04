<?php

declare(strict_types=1);

namespace Madcoders\SyliusRmaPlugin\Fixture;

use Sylius\Bundle\CoreBundle\Fixture\AbstractResourceFixture;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

final class OrderReturnItemFixture extends AbstractResourceFixture
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'madcoders_rma_order_return_item';
    }

    /**
     * {@inheritdoc}
     */
    protected function configureResourceNode(ArrayNodeDefinition $resourceNode): void
    {
        $resourceNode
            ->children()
                ->scalarNode('return_number')->cannotBeEmpty()->end()
                ->scalarNode('product_sku')->cannotBeEmpty()->end()
                ->scalarNode('product_name')->cannotBeEmpty()->end()
                ->scalarNode('return_qty')->cannotBeEmpty()->end()
                ->scalarNode('unit_price')->end()
        ;
    }
}
