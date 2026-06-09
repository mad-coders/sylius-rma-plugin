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

namespace Tests\Madcoders\SyliusRmaPlugin\Unit\Services\Reason;

use Doctrine\Common\Collections\ArrayCollection;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnReasonInterface;
use Madcoders\SyliusRmaPlugin\Services\Reason\DeadlineReturnReasonEligibilityChecker;
use Prophecy\PhpUnit\ProphecyTrait;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\ShipmentInterface;
use Tests\Madcoders\SyliusRmaPlugin\Unit\UnitTestCase;

class DeadlineReturnReasonEligibilityCheckerTest extends UnitTestCase
{
    use ProphecyTrait;

    /** @test */
    function it_keeps_a_reason_eligible_within_the_deadline()
    {
        $checker = new DeadlineReturnReasonEligibilityChecker();

        $this->assertTrue($checker->isEligible($this->orderShippedDaysAgo(5), $this->reasonWithDeadline(14)));
    }

    /** @test */
    function it_keeps_a_reason_eligible_on_the_exact_deadline_day()
    {
        $checker = new DeadlineReturnReasonEligibilityChecker();

        $this->assertTrue($checker->isEligible($this->orderShippedDaysAgo(14), $this->reasonWithDeadline(14)));
    }

    /** @test */
    function it_rejects_a_reason_once_the_total_elapsed_days_exceed_the_deadline()
    {
        $checker = new DeadlineReturnReasonEligibilityChecker();

        $this->assertFalse($checker->isEligible($this->orderShippedDaysAgo(40), $this->reasonWithDeadline(14)));
    }

    /** @test */
    function it_counts_total_days_across_month_and_year_boundaries()
    {
        $checker = new DeadlineReturnReasonEligibilityChecker();

        // ~one year and a few days: the day-of-month component is small, total days are large
        $this->assertFalse($checker->isEligible($this->orderShippedDaysAgo(368), $this->reasonWithDeadline(14)));
    }

    /** @test */
    function it_rejects_a_reason_that_has_no_deadline()
    {
        $checker = new DeadlineReturnReasonEligibilityChecker();

        $this->assertFalse($checker->isEligible($this->orderShippedDaysAgo(1), $this->reasonWithDeadline(null)));
    }

    /** @test */
    function it_rejects_a_reason_when_the_order_has_no_shipment()
    {
        $checker = new DeadlineReturnReasonEligibilityChecker();

        $order = $this->prophesize(OrderInterface::class);
        $order->getShipments()->willReturn(new ArrayCollection([]));

        $this->assertFalse($checker->isEligible($order->reveal(), $this->reasonWithDeadline(14)));
    }

    /** @test */
    function it_rejects_a_reason_when_the_shipment_has_no_shipped_date()
    {
        $checker = new DeadlineReturnReasonEligibilityChecker();

        $shipment = $this->prophesize(ShipmentInterface::class);
        $shipment->getShippedAt()->willReturn(null);

        $order = $this->prophesize(OrderInterface::class);
        $order->getShipments()->willReturn(new ArrayCollection([$shipment->reveal()]));

        $this->assertFalse($checker->isEligible($order->reveal(), $this->reasonWithDeadline(14)));
    }

    private function reasonWithDeadline(?int $deadlineToReturn): OrderReturnReasonInterface
    {
        $reason = $this->prophesize(OrderReturnReasonInterface::class);
        $reason->getDeadlineToReturn()->willReturn($deadlineToReturn);

        return $reason->reveal();
    }

    private function orderShippedDaysAgo(int $days): OrderInterface
    {
        $shippedAt = (new \DateTimeImmutable())->modify(sprintf('-%d days', $days));

        $shipment = $this->prophesize(ShipmentInterface::class);
        $shipment->getShippedAt()->willReturn($shippedAt);

        $order = $this->prophesize(OrderInterface::class);
        $order->getShipments()->willReturn(new ArrayCollection([$shipment->reveal()]));

        return $order->reveal();
    }
}
