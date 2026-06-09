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
     * @inheritdoc
     */
    public function getName(): string
    {
        return 'madcoders_rma_order_return';
    }

    /**
     * @inheritdoc
     */
    protected function configureResourceNode(ArrayNodeDefinition $resourceNode): void
    {
        $nodeBuilder = $resourceNode->children();
        $nodeBuilder->scalarNode('channel_code')->cannotBeEmpty();
        $nodeBuilder->scalarNode('order_number')->cannotBeEmpty();
        $nodeBuilder->scalarNode('return_number')->cannotBeEmpty();
        $nodeBuilder->scalarNode('return_reason')->cannotBeEmpty();
        $nodeBuilder->scalarNode('return_consents');
        $nodeBuilder->scalarNode('city');
        $nodeBuilder->scalarNode('postcode');
        $nodeBuilder->scalarNode('street');
        $nodeBuilder->scalarNode('phone_number');
        $nodeBuilder->scalarNode('customer_ip');
        $nodeBuilder->scalarNode('customer_number');
    }
}
