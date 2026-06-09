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

final class OrderReturnItemFixture extends AbstractResourceFixture
{
    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return 'madcoders_rma_order_return_item';
    }

    /**
     * @inheritdoc
     */
    protected function configureResourceNode(ArrayNodeDefinition $resourceNode): void
    {
        $nodeBuilder = $resourceNode->children();
        $nodeBuilder->scalarNode('return_number')->cannotBeEmpty();
        $nodeBuilder->scalarNode('product_sku')->cannotBeEmpty();
        $nodeBuilder->scalarNode('product_name')->cannotBeEmpty();
        $nodeBuilder->scalarNode('return_qty')->cannotBeEmpty();
        $nodeBuilder->scalarNode('unit_price');
    }
}
