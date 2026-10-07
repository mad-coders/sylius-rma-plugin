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

namespace Tests\Madcoders\SyliusRmaPlugin\Unit\Services\GracePeriod;

use Doctrine\Common\Collections\ArrayCollection;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnReasonGracePeriod;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnReasonGracePeriodLog;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnReasonInterface;
use Madcoders\SyliusRmaPlugin\Services\GracePeriod\OrderGracePeriodOverviewProvider;
use Prophecy\PhpUnit\ProphecyTrait;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\ShipmentInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Tests\Madcoders\SyliusRmaPlugin\Unit\UnitTestCase;

class OrderGracePeriodOverviewProviderTest extends UnitTestCase
{
    use ProphecyTrait;

    /** @test */
    function it_lists_every_enabled_reason_with_its_grace_period_and_last_return_day()
    {
        $order = $this->orderShippedAt(new \DateTimeImmutable('2026-09-01 10:00:00'));
        $damaged = $this->reason('damaged', 14);
        $wrongSize = $this->reason('wrong_size', 30);
        $gracePeriod = $this->gracePeriod($order, $damaged, 20);

        $provider = $this->provider([$damaged, $wrongSize], [$gracePeriod], []);
        $rows = $provider->getRows($order);

        $this->assertCount(2, $rows);

        $this->assertSame($damaged, $rows[0]->reason);
        $this->assertSame(14, $rows[0]->baseDays);
        $this->assertSame($gracePeriod, $rows[0]->gracePeriod);
        $this->assertSame(20, $rows[0]->getExtraDays());
        $this->assertSame('2026-10-05', $rows[0]->lastReturnDay?->format('Y-m-d'));

        $this->assertSame($wrongSize, $rows[1]->reason);
        $this->assertNull($rows[1]->gracePeriod);
        $this->assertNull($rows[1]->getExtraDays());
        $this->assertSame('2026-10-01', $rows[1]->lastReturnDay?->format('Y-m-d'));
    }

    /** @test */
    function it_has_no_last_return_day_for_an_order_that_was_not_shipped()
    {
        $order = $this->orderShippedAt(null);
        $provider = $this->provider([$this->reason('damaged', 14)], [], []);

        $rows = $provider->getRows($order);

        $this->assertNull($rows[0]->lastReturnDay);
        $this->assertNull($provider->getShippedAt($order));
    }

    /** @test */
    function it_returns_the_history_from_the_repository_newest_first()
    {
        $order = $this->orderShippedAt(null);
        $newer = new OrderReturnReasonGracePeriodLog();
        $older = new OrderReturnReasonGracePeriodLog();

        $reasonRepository = $this->prophesize(RepositoryInterface::class);
        $gracePeriodRepository = $this->prophesize(RepositoryInterface::class);
        $logRepository = $this->prophesize(RepositoryInterface::class);
        $logRepository->findBy(['order' => $order], ['createdAt' => 'DESC', 'id' => 'DESC'])->willReturn([$newer, $older]);

        $provider = new OrderGracePeriodOverviewProvider(
            $reasonRepository->reveal(),
            $gracePeriodRepository->reveal(),
            $logRepository->reveal(),
        );

        $this->assertSame([$newer, $older], $provider->getHistory($order));
    }

    /**
     * @param OrderReturnReasonInterface[] $reasons
     * @param OrderReturnReasonGracePeriod[] $gracePeriods
     * @param OrderReturnReasonGracePeriodLog[] $history
     */
    private function provider(array $reasons, array $gracePeriods, array $history): OrderGracePeriodOverviewProvider
    {
        $reasonRepository = $this->prophesize(RepositoryInterface::class);
        $reasonRepository->findBy(['enabled' => true], ['position' => 'ASC'])->willReturn($reasons);

        $gracePeriodRepository = $this->prophesize(RepositoryInterface::class);
        $gracePeriodRepository->findBy(\Prophecy\Argument::any())->willReturn($gracePeriods);

        $logRepository = $this->prophesize(RepositoryInterface::class);
        $logRepository->findBy(\Prophecy\Argument::cetera())->willReturn($history);

        return new OrderGracePeriodOverviewProvider(
            $reasonRepository->reveal(),
            $gracePeriodRepository->reveal(),
            $logRepository->reveal(),
        );
    }

    private function orderShippedAt(?\DateTimeImmutable $shippedAt): OrderInterface
    {
        $shipment = $this->prophesize(ShipmentInterface::class);
        $shipment->getShippedAt()->willReturn($shippedAt);

        $order = $this->prophesize(OrderInterface::class);
        $order->getShipments()->willReturn(new ArrayCollection([$shipment->reveal()]));

        return $order->reveal();
    }

    private function reason(string $code, int $deadlineToReturn): OrderReturnReasonInterface
    {
        $reason = $this->prophesize(OrderReturnReasonInterface::class);
        $reason->getCode()->willReturn($code);
        $reason->getDeadlineToReturn()->willReturn($deadlineToReturn);

        return $reason->reveal();
    }

    private function gracePeriod(OrderInterface $order, OrderReturnReasonInterface $reason, int $extraDays): OrderReturnReasonGracePeriod
    {
        $gracePeriod = new OrderReturnReasonGracePeriod();
        $gracePeriod->setOrder($order);
        $gracePeriod->setReason($reason);
        $gracePeriod->setExtraDays($extraDays);

        return $gracePeriod;
    }
}
