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

namespace Madcoders\SyliusRmaPlugin\Provider;

use Sylius\Component\Core\Model\OrderInterface;

interface OrderByNumberProviderInterface
{
    /**
     * @param string $orderNumber
     *
     * @return OrderInterface|null
     */
    public function findOneByNumber(string $orderNumber): ?OrderInterface;
}
