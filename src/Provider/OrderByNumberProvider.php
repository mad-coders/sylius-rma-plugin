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
use Sylius\Component\Core\Repository\OrderRepositoryInterface;

final readonly class OrderByNumberProvider implements OrderByNumberProviderInterface
{
    private const ORDER_PREFIX_SIGN = '#';

    /**
     * @param OrderRepositoryInterface<OrderInterface> $orderRepository
     */
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private string $prefixSign = self::ORDER_PREFIX_SIGN,
    ) {
    }

    public function findOneByNumber(string $orderNumber): ?OrderInterface
    {
        $orderNumber = trim(str_replace([$this->prefixSign], '', $orderNumber));
        $order = $this->orderRepository->findOneByNumber($orderNumber);
        if (!$order instanceof OrderInterface) {
            $order = $this->orderRepository->findOneByNumber($this->prefixSign . $orderNumber);
        }

        if (!$order instanceof OrderInterface) {
            return null;
        }

        return $order;
    }
}
