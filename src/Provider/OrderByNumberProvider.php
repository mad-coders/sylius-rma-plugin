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

final class OrderByNumberProvider implements OrderByNumberProviderInterface
{
    private const ORDER_PREFIX_SIGN = '#';

    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /** @var string */
    private $prefixSign;

    public function __construct(OrderRepositoryInterface $orderRepository, string $prefixSign = self::ORDER_PREFIX_SIGN)
    {
        $this->orderRepository = $orderRepository;
        $this->prefixSign = $prefixSign;
    }

    public function findOneByNumber(string $orderNumber): ?OrderInterface
    {
        $orderNumber = trim(str_replace([$this->prefixSign], '', $orderNumber));
        if (!$order = $this->orderRepository->findOneByNumber($orderNumber)) {
            $order = $this->orderRepository->findOneByNumber($this->prefixSign . $orderNumber);
        }

        if (!$order instanceof OrderInterface) {
            return null;
        }

        return $order;
    }
}
