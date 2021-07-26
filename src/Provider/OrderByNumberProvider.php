<?php

declare(strict_types=1);

namespace Madcoders\SyliusRmaPlugin\Provider;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;

final class OrderByNumberProvider implements OrderByNumberProviderInterface
{
    private const ORDER_PREFIX_SIGN = '#';

    /** @var OrderRepositoryInterface */
    private $orderRepository;

    public function __construct(
        OrderRepositoryInterface $orderRepository
    )
    {
        $this->orderRepository = $orderRepository;
    }

    public function findOneByNumber(string $orderNumber): ?OrderInterface
    {
        $orderNumber = trim(str_replace([self::ORDER_PREFIX_SIGN], '', $orderNumber));
        if (!$order = $this->orderRepository->findOneByNumber($orderNumber)) {
            $order = $this->orderRepository->findOneByNumber(self::ORDER_PREFIX_SIGN . $orderNumber);
        }

        if (!$order instanceof OrderInterface) {
            return null;
        }

        return $order;
    }
}
