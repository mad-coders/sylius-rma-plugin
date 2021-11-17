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
                ->scalarNode('return_consents')->end()
                ->scalarNode('city')->end()
                ->scalarNode('postcode')->end()
                ->scalarNode('street')->end()
                ->scalarNode('phone_number')->end()
                ->scalarNode('customer_ip')->end()
        ;
    }
}
