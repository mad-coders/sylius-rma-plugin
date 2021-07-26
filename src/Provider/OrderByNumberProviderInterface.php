<?php

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
