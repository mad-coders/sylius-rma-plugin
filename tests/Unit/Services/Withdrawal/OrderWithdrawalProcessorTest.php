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

namespace Tests\Madcoders\SyliusRmaPlugin\Unit\Services\Withdrawal;

use Madcoders\SyliusRmaPlugin\Entity\OrderReturnInterface;
use Madcoders\SyliusRmaPlugin\Services\Withdrawal\OrderWithdrawalProcessor;
use Prophecy\PhpUnit\ProphecyTrait;
use SM\Factory\FactoryInterface as StateMachineFactoryInterface;
use SM\StateMachine\StateMachineInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Order\OrderTransitions;
use Tests\Madcoders\SyliusRmaPlugin\Unit\UnitTestCase;

class OrderWithdrawalProcessorTest extends UnitTestCase
{
    use ProphecyTrait;

    /** @test */
    function it_cancels_the_order_and_withdraws_the_return()
    {
        $order = $this->prophesize(OrderInterface::class);
        $orderReturn = $this->prophesize(OrderReturnInterface::class);

        $orderSm = $this->prophesize(StateMachineInterface::class);
        $orderSm->can(OrderTransitions::TRANSITION_CANCEL)->willReturn(true);
        $orderSm->apply(OrderTransitions::TRANSITION_CANCEL)->shouldBeCalledOnce()->willReturn(true);

        $returnSm = $this->prophesize(StateMachineInterface::class);
        $returnSm->can(OrderReturnInterface::TRANSITION_WITHDRAW)->willReturn(true);
        $returnSm->apply(OrderReturnInterface::TRANSITION_WITHDRAW)->shouldBeCalledOnce()->willReturn(true);

        $factory = $this->factory($order->reveal(), $orderSm->reveal(), $orderReturn->reveal(), $returnSm->reveal());

        (new OrderWithdrawalProcessor($factory))->process($order->reveal(), $orderReturn->reveal());
    }

    /** @test */
    function it_aborts_without_withdrawing_when_the_order_cannot_be_cancelled()
    {
        $order = $this->prophesize(OrderInterface::class);
        $order->getNumber()->willReturn('000000011');
        $orderReturn = $this->prophesize(OrderReturnInterface::class);

        $orderSm = $this->prophesize(StateMachineInterface::class);
        $orderSm->can(OrderTransitions::TRANSITION_CANCEL)->willReturn(false);
        $orderSm->apply(OrderTransitions::TRANSITION_CANCEL)->shouldNotBeCalled();

        $returnSm = $this->prophesize(StateMachineInterface::class);
        $returnSm->apply(OrderReturnInterface::TRANSITION_WITHDRAW)->shouldNotBeCalled();

        $factory = $this->factory($order->reveal(), $orderSm->reveal(), $orderReturn->reveal(), $returnSm->reveal());

        $this->expectException(\RuntimeException::class);

        (new OrderWithdrawalProcessor($factory))->process($order->reveal(), $orderReturn->reveal());
    }

    private function factory(
        OrderInterface $order,
        StateMachineInterface $orderSm,
        OrderReturnInterface $orderReturn,
        StateMachineInterface $returnSm,
    ): StateMachineFactoryInterface {
        $factory = $this->prophesize(StateMachineFactoryInterface::class);
        $factory->get($order, OrderTransitions::GRAPH)->willReturn($orderSm);
        $factory->get($orderReturn, OrderReturnInterface::GRAPH)->willReturn($returnSm);

        return $factory->reveal();
    }
}
