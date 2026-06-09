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
use Madcoders\SyliusRmaPlugin\Services\Reason\ChoiceProvider;
use Madcoders\SyliusRmaPlugin\Services\Reason\DeadlineReturnReasonEligibilityChecker;
use Prophecy\PhpUnit\ProphecyTrait;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\ShipmentInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Tests\Madcoders\SyliusRmaPlugin\Unit\UnitTestCase;

class ChoiceProviderTest extends UnitTestCase
{
    use ProphecyTrait;

    /** @test */
    function it_keeps_a_reason_while_within_its_deadline()
    {
        // given an order shipped 5 days ago and a reason allowing 14 days
        $order = $this->fulfilledOrderShippedDaysAgo(5);
        $provider = $this->providerWithReasons([$this->reason('reason_14', 'Reason 14', 14)]);

        // when
        $reasons = $provider->createAvailableReasons($order);

        // then
        $this->assertSame(['reason_14' => 'Reason 14'], $reasons);
    }

    /** @test */
    function it_excludes_a_reason_when_more_than_a_month_has_elapsed_since_shipment()
    {
        // given an order shipped 40 days ago and a reason allowing only 14 days
        $order = $this->fulfilledOrderShippedDaysAgo(40);
        $provider = $this->providerWithReasons([$this->reason('reason_14', 'Reason 14', 14)]);

        // when
        $reasons = $provider->createAvailableReasons($order);

        // then 40 total elapsed days exceed the 14 day deadline, so the reason must be unavailable
        $this->assertSame([], $reasons);
    }

    /** @test */
    function it_excludes_a_reason_near_a_month_anniversary_long_after_the_deadline()
    {
        // given an order shipped one year and a few days ago (day component is small, total days are large)
        $order = $this->fulfilledOrderShippedDaysAgo(368);
        $provider = $this->providerWithReasons([$this->reason('reason_14', 'Reason 14', 14)]);

        // when
        $reasons = $provider->createAvailableReasons($order);

        // then eligibility must not reappear at a month/year anniversary
        $this->assertSame([], $reasons);
    }

    private function fulfilledOrderShippedDaysAgo(int $days): OrderInterface
    {
        $shippedAt = (new \DateTime())->modify(sprintf('-%d days', $days));

        $shipment = $this->prophesize(ShipmentInterface::class);
        $shipment->getShippedAt()->willReturn($shippedAt);

        $order = $this->prophesize(OrderInterface::class);
        $order->getShipments()->willReturn(new ArrayCollection([$shipment->reveal()]));
        $order->getState()->willReturn(OrderInterface::STATE_FULFILLED);

        return $order->reveal();
    }

    private function reason(string $code, string $name, int $deadlineToReturn): OrderReturnReasonInterface
    {
        $reason = $this->prophesize(OrderReturnReasonInterface::class);
        $reason->getCode()->willReturn($code);
        $reason->getName()->willReturn($name);
        $reason->getDeadlineToReturn()->willReturn($deadlineToReturn);

        return $reason->reveal();
    }

    /**
     * @param OrderReturnReasonInterface[] $reasons
     */
    private function providerWithReasons(array $reasons): ChoiceProvider
    {
        $reasonRepository = $this->prophesize(RepositoryInterface::class);
        $reasonRepository->findBy(['enabled' => true])->willReturn($reasons);

        return new ChoiceProvider(
            $reasonRepository->reveal(),
            $this->prophesize(OrderRepositoryInterface::class)->reveal(),
            new DeadlineReturnReasonEligibilityChecker(),
        );
    }
}
