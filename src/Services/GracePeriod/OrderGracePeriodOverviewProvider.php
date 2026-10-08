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

namespace Madcoders\SyliusRmaPlugin\Services\GracePeriod;

use Madcoders\SyliusRmaPlugin\Entity\OrderReturnReasonGracePeriodInterface;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnReasonGracePeriodLogInterface;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnReasonInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\ShipmentInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

/**
 * Read model for the admin order grace period panel (#67): every enabled reason with its base
 * deadline, the grace period granted for the order and the resulting last return day, plus the
 * audit history newest first.
 */
final readonly class OrderGracePeriodOverviewProvider
{
    /**
     * @param RepositoryInterface<OrderReturnReasonInterface> $reasonRepository
     * @param RepositoryInterface<OrderReturnReasonGracePeriodInterface> $gracePeriodRepository
     * @param RepositoryInterface<OrderReturnReasonGracePeriodLogInterface> $gracePeriodLogRepository
     */
    public function __construct(
        private RepositoryInterface $reasonRepository,
        private RepositoryInterface $gracePeriodRepository,
        private RepositoryInterface $gracePeriodLogRepository,
    ) {
    }

    /**
     * @return list<GracePeriodOverviewRow>
     */
    public function getRows(OrderInterface $order): array
    {
        $gracePeriods = [];
        /** @var OrderReturnReasonGracePeriodInterface $gracePeriod */
        foreach ($this->gracePeriodRepository->findBy(['order' => $order]) as $gracePeriod) {
            $gracePeriods[(string) $gracePeriod->getReason()->getCode()] = $gracePeriod;
        }

        $shippedAt = $this->getShippedAt($order);
        $rows = [];
        /** @var OrderReturnReasonInterface $reason */
        foreach ($this->reasonRepository->findBy(['enabled' => true], ['position' => 'ASC']) as $reason) {
            $baseDays = $reason->getDeadlineToReturn() ?? 0;
            $gracePeriod = $gracePeriods[(string) $reason->getCode()] ?? null;
            $totalDays = $baseDays + ($gracePeriod?->getExtraDays() ?? 0);

            $rows[] = new GracePeriodOverviewRow(
                $reason,
                $baseDays,
                $gracePeriod,
                $shippedAt?->add(new \DateInterval(sprintf('P%dD', $totalDays))),
            );
        }

        return $rows;
    }

    /**
     * @return list<OrderReturnReasonGracePeriodLogInterface>
     */
    public function getHistory(OrderInterface $order): array
    {
        return $this->gracePeriodLogRepository->findBy(
            ['order' => $order],
            ['createdAt' => 'DESC', 'id' => 'DESC'],
        );
    }

    /**
     * Same reference date the eligibility check uses: the first shipment's shipped date.
     */
    public function getShippedAt(OrderInterface $order): ?\DateTimeImmutable
    {
        $shipment = $order->getShipments()->first();
        if (!$shipment instanceof ShipmentInterface) {
            return null;
        }

        $shippedAt = $shipment->getShippedAt();

        return null === $shippedAt ? null : \DateTimeImmutable::createFromInterface($shippedAt);
    }
}
