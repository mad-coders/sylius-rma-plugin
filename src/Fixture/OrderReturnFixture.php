<?php

declare(strict_types=1);

namespace Madcoders\SyliusRmaPlugin\Fixture;

use Sylius\Bundle\CoreBundle\Fixture\AbstractResourceFixture;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

final class OrderReturnFixture extends AbstractResourceFixture
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'madcoders_rma_order_return';
    }

    /**
     * {@inheritdoc}
     */
    protected function configureResourceNode(ArrayNodeDefinition $resourceNode): void
    {
        $resourceNode
            ->children()
                ->scalarNode('channel_code')->cannotBeEmpty()->end()
                ->scalarNode('order_number')->cannotBeEmpty()->end()
                ->scalarNode('return_number')->cannotBeEmpty()->end()
                ->scalarNode('return_reason')->cannotBeEmpty()->end()
                ->scalarNode('return_consent')->cannotBeEmpty()->end()
                ->scalarNode('return_consent_label')->end()
                ->scalarNode('city')->end()
                ->scalarNode('postcode')->end()
                ->scalarNode('street')->end()
                ->scalarNode('phone_number')->end()
                ->scalarNode('customer_ip')->end()
        ;
    }
}
