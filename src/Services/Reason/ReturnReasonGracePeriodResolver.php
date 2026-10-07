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

namespace Madcoders\SyliusRmaPlugin\Services\Reason;

use Madcoders\SyliusRmaPlugin\Entity\OrderReturnReasonGracePeriodInterface;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnReasonInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Contracts\Service\ResetInterface;

/**
 * Looks up an order's grace periods with one query per order and remembers them for the rest
 * of the request: the customer order list checks every order against every enabled reason.
 * Reset between requests through the kernel.reset tag (ResetInterface + autoconfigure).
 */
final class ReturnReasonGracePeriodResolver implements ReturnReasonGracePeriodResolverInterface, ResetInterface
{
    /** @var array<int, array<string, int>> extra days by reason code, per order id */
    private array $extraDaysByOrder = [];

    /**
     * @param RepositoryInterface<OrderReturnReasonGracePeriodInterface> $gracePeriodRepository
     */
    public function __construct(private readonly RepositoryInterface $gracePeriodRepository)
    {
    }

    public function getExtraDays(OrderInterface $order, OrderReturnReasonInterface $reason): int
    {
        $orderId = $order->getId();
        $reasonCode = $reason->getCode();
        if (!is_int($orderId) || null === $reasonCode) {
            return 0;
        }

        if (!isset($this->extraDaysByOrder[$orderId])) {
            $this->extraDaysByOrder[$orderId] = $this->loadExtraDays($order);
        }

        return $this->extraDaysByOrder[$orderId][$reasonCode] ?? 0;
    }

    public function reset(): void
    {
        $this->extraDaysByOrder = [];
    }

    /**
     * @return array<string, int>
     */
    private function loadExtraDays(OrderInterface $order): array
    {
        $extraDays = [];

        /** @var OrderReturnReasonGracePeriodInterface $gracePeriod */
        foreach ($this->gracePeriodRepository->findBy(['order' => $order]) as $gracePeriod) {
            $reasonCode = $gracePeriod->getReason()->getCode();
            if (null !== $reasonCode) {
                $extraDays[$reasonCode] = $gracePeriod->getExtraDays();
            }
        }

        return $extraDays;
    }
}
