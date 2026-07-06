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
use Sylius\Abstraction\StateMachine\StateMachineInterface;
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

        $stateMachine = $this->prophesize(StateMachineInterface::class);
        $stateMachine->can($order->reveal(), OrderTransitions::GRAPH, OrderTransitions::TRANSITION_CANCEL)->willReturn(true);
        $stateMachine->can($orderReturn->reveal(), OrderReturnInterface::GRAPH, OrderReturnInterface::TRANSITION_WITHDRAW)->willReturn(true);
        $stateMachine->apply($order->reveal(), OrderTransitions::GRAPH, OrderTransitions::TRANSITION_CANCEL)->shouldBeCalledOnce();
        $stateMachine->apply($orderReturn->reveal(), OrderReturnInterface::GRAPH, OrderReturnInterface::TRANSITION_WITHDRAW)->shouldBeCalledOnce();

        (new OrderWithdrawalProcessor($stateMachine->reveal()))->process($order->reveal(), $orderReturn->reveal());
    }

    /** @test */
    function it_aborts_without_withdrawing_when_the_order_cannot_be_cancelled()
    {
        $order = $this->prophesize(OrderInterface::class);
        $order->getNumber()->willReturn('000000011');
        $orderReturn = $this->prophesize(OrderReturnInterface::class);

        $stateMachine = $this->prophesize(StateMachineInterface::class);
        $stateMachine->can($order->reveal(), OrderTransitions::GRAPH, OrderTransitions::TRANSITION_CANCEL)->willReturn(false);
        $stateMachine->apply($order->reveal(), OrderTransitions::GRAPH, OrderTransitions::TRANSITION_CANCEL)->shouldNotBeCalled();
        $stateMachine->apply($orderReturn->reveal(), OrderReturnInterface::GRAPH, OrderReturnInterface::TRANSITION_WITHDRAW)->shouldNotBeCalled();

        $this->expectException(\RuntimeException::class);

        (new OrderWithdrawalProcessor($stateMachine->reveal()))->process($order->reveal(), $orderReturn->reveal());
    }
}
