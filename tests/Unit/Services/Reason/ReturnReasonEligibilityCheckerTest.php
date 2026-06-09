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
use Madcoders\SyliusRmaPlugin\Services\Reason\ReturnDeadlineCheckerInterface;
use Madcoders\SyliusRmaPlugin\Services\Reason\ReturnReasonEligibilityChecker;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\ShipmentInterface;
use Tests\Madcoders\SyliusRmaPlugin\Unit\UnitTestCase;

class ReturnReasonEligibilityCheckerTest extends UnitTestCase
{
    use ProphecyTrait;

    /** @test */
    function it_rejects_a_reason_when_the_order_has_no_shipment()
    {
        $deadlineChecker = $this->prophesize(ReturnDeadlineCheckerInterface::class);
        $deadlineChecker->isWithinDeadline(Argument::cetera())->shouldNotBeCalled();

        $order = $this->prophesize(OrderInterface::class);
        $order->getShipments()->willReturn(new ArrayCollection([]));

        $checker = new ReturnReasonEligibilityChecker($deadlineChecker->reveal());

        $this->assertFalse($checker->isEligible($order->reveal(), $this->reason()));
    }

    /** @test */
    function it_rejects_a_reason_when_the_shipment_has_no_shipped_date()
    {
        $deadlineChecker = $this->prophesize(ReturnDeadlineCheckerInterface::class);
        $deadlineChecker->isWithinDeadline(Argument::cetera())->shouldNotBeCalled();

        $shipment = $this->prophesize(ShipmentInterface::class);
        $shipment->getShippedAt()->willReturn(null);

        $checker = new ReturnReasonEligibilityChecker($deadlineChecker->reveal());

        $this->assertFalse($checker->isEligible($this->orderWith($shipment)->reveal(), $this->reason()));
    }

    /** @test */
    function it_delegates_to_the_deadline_checker_with_the_shipped_date()
    {
        $shippedAt = new \DateTimeImmutable('-5 days');
        $reason = $this->reason();

        $shipment = $this->prophesize(ShipmentInterface::class);
        $shipment->getShippedAt()->willReturn($shippedAt);

        $deadlineChecker = $this->prophesize(ReturnDeadlineCheckerInterface::class);
        $deadlineChecker->isWithinDeadline($reason, $shippedAt)->willReturn(true)->shouldBeCalledOnce();

        $checker = new ReturnReasonEligibilityChecker($deadlineChecker->reveal());

        $this->assertTrue($checker->isEligible($this->orderWith($shipment)->reveal(), $reason));
    }

    /** @test */
    function it_rejects_a_reason_when_the_deadline_checker_rejects_it()
    {
        $shippedAt = new \DateTimeImmutable('-40 days');
        $reason = $this->reason();

        $shipment = $this->prophesize(ShipmentInterface::class);
        $shipment->getShippedAt()->willReturn($shippedAt);

        $deadlineChecker = $this->prophesize(ReturnDeadlineCheckerInterface::class);
        $deadlineChecker->isWithinDeadline($reason, $shippedAt)->willReturn(false)->shouldBeCalledOnce();

        $checker = new ReturnReasonEligibilityChecker($deadlineChecker->reveal());

        $this->assertFalse($checker->isEligible($this->orderWith($shipment)->reveal(), $reason));
    }

    private function reason(): OrderReturnReasonInterface
    {
        return $this->prophesize(OrderReturnReasonInterface::class)->reveal();
    }

    /**
     * @param ObjectProphecy<ShipmentInterface> $shipment
     *
     * @return ObjectProphecy<OrderInterface>
     */
    private function orderWith(ObjectProphecy $shipment): ObjectProphecy
    {
        $order = $this->prophesize(OrderInterface::class);
        $order->getShipments()->willReturn(new ArrayCollection([$shipment->reveal()]));

        return $order;
    }
}
