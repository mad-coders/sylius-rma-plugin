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

use Madcoders\SyliusRmaPlugin\Entity\OrderReturnReasonGracePeriod;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnReasonInterface;
use Madcoders\SyliusRmaPlugin\Services\Reason\ReturnReasonGracePeriodResolver;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Tests\Madcoders\SyliusRmaPlugin\Unit\UnitTestCase;

class ReturnReasonGracePeriodResolverTest extends UnitTestCase
{
    use ProphecyTrait;

    /** @test */
    function it_returns_the_extra_days_granted_for_the_order_and_reason()
    {
        $order = $this->order(7);
        $repository = $this->prophesize(RepositoryInterface::class);
        $repository->findBy(['order' => $order])->willReturn([$this->gracePeriod($order, 'damaged', 20)]);

        $resolver = new ReturnReasonGracePeriodResolver($repository->reveal());

        $this->assertSame(20, $resolver->getExtraDays($order, $this->reason('damaged')));
    }

    /** @test */
    function it_returns_zero_for_a_reason_without_a_grace_period()
    {
        $order = $this->order(7);
        $repository = $this->prophesize(RepositoryInterface::class);
        $repository->findBy(['order' => $order])->willReturn([$this->gracePeriod($order, 'damaged', 20)]);

        $resolver = new ReturnReasonGracePeriodResolver($repository->reveal());

        $this->assertSame(0, $resolver->getExtraDays($order, $this->reason('wrong_size')));
    }

    /** @test */
    function it_queries_the_grace_periods_of_an_order_only_once()
    {
        $order = $this->order(7);
        $repository = $this->prophesize(RepositoryInterface::class);
        $repository->findBy(['order' => $order])->willReturn([$this->gracePeriod($order, 'damaged', 20)])->shouldBeCalledOnce();

        $resolver = new ReturnReasonGracePeriodResolver($repository->reveal());

        $this->assertSame(20, $resolver->getExtraDays($order, $this->reason('damaged')));
        $this->assertSame(0, $resolver->getExtraDays($order, $this->reason('wrong_size')));
        $this->assertSame(20, $resolver->getExtraDays($order, $this->reason('damaged')));
    }

    /** @test */
    function it_queries_again_after_a_reset()
    {
        $order = $this->order(7);
        $repository = $this->prophesize(RepositoryInterface::class);
        $repository->findBy(['order' => $order])->willReturn([], [$this->gracePeriod($order, 'damaged', 20)])->shouldBeCalledTimes(2);

        $resolver = new ReturnReasonGracePeriodResolver($repository->reveal());

        $this->assertSame(0, $resolver->getExtraDays($order, $this->reason('damaged')));
        $resolver->reset();
        $this->assertSame(20, $resolver->getExtraDays($order, $this->reason('damaged')));
    }

    /** @test */
    function it_returns_zero_without_querying_for_an_order_that_is_not_persisted()
    {
        $repository = $this->prophesize(RepositoryInterface::class);
        $repository->findBy(Argument::any())->shouldNotBeCalled();

        $resolver = new ReturnReasonGracePeriodResolver($repository->reveal());

        $this->assertSame(0, $resolver->getExtraDays($this->order(null), $this->reason('damaged')));
    }

    private function order(?int $id): OrderInterface
    {
        $order = $this->prophesize(OrderInterface::class);
        $order->getId()->willReturn($id);

        return $order->reveal();
    }

    private function reason(string $code): OrderReturnReasonInterface
    {
        $reason = $this->prophesize(OrderReturnReasonInterface::class);
        $reason->getCode()->willReturn($code);

        return $reason->reveal();
    }

    private function gracePeriod(OrderInterface $order, string $reasonCode, int $extraDays): OrderReturnReasonGracePeriod
    {
        $gracePeriod = new OrderReturnReasonGracePeriod();
        $gracePeriod->setOrder($order);
        $gracePeriod->setReason($this->reason($reasonCode));
        $gracePeriod->setExtraDays($extraDays);

        return $gracePeriod;
    }
}
