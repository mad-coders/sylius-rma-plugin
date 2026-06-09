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

use Madcoders\SyliusRmaPlugin\Services\Withdrawal\WithdrawalEligibilityChecker;
use Madcoders\SyliusRmaPlugin\Services\Withdrawal\WithdrawalPath;
use Prophecy\PhpUnit\ProphecyTrait;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\OrderPaymentStates;
use Sylius\Component\Core\OrderShippingStates;
use Tests\Madcoders\SyliusRmaPlugin\Unit\UnitTestCase;

class WithdrawalEligibilityCheckerTest extends UnitTestCase
{
    use ProphecyTrait;

    /** @test */
    function it_does_not_allow_withdrawal_for_a_fulfilled_order()
    {
        $order = $this->order(OrderInterface::STATE_FULFILLED, OrderShippingStates::STATE_SHIPPED, OrderPaymentStates::STATE_PAID);

        $this->assertSame(WithdrawalPath::NONE, $this->checker(true)->resolvePath($order));
    }

    /** @test */
    function it_does_not_allow_withdrawal_for_a_partially_shipped_order()
    {
        $order = $this->order(OrderInterface::STATE_NEW, OrderShippingStates::STATE_PARTIALLY_SHIPPED, OrderPaymentStates::STATE_PAID);

        $this->assertSame(WithdrawalPath::NONE, $this->checker(true)->resolvePath($order));
    }

    /** @test */
    function it_does_not_allow_withdrawal_for_a_cancelled_order()
    {
        $order = $this->order(OrderInterface::STATE_CANCELLED, OrderShippingStates::STATE_CANCELLED, OrderPaymentStates::STATE_CANCELLED);

        $this->assertSame(WithdrawalPath::NONE, $this->checker(true)->resolvePath($order));
    }

    /** @test */
    function paid_not_shipped_order_takes_the_admin_request_path()
    {
        $order = $this->order(OrderInterface::STATE_NEW, OrderShippingStates::STATE_READY, OrderPaymentStates::STATE_PAID);

        $this->assertSame(WithdrawalPath::PAID_REQUEST, $this->checker(true)->resolvePath($order));
    }

    /** @test */
    function authorized_not_shipped_order_takes_the_admin_request_path()
    {
        $order = $this->order(OrderInterface::STATE_NEW, OrderShippingStates::STATE_READY, OrderPaymentStates::STATE_AUTHORIZED);

        $this->assertSame(WithdrawalPath::PAID_REQUEST, $this->checker(true)->resolvePath($order));
    }

    /** @test */
    function unpaid_not_shipped_order_auto_cancels_when_the_flag_is_on()
    {
        $order = $this->order(OrderInterface::STATE_NEW, OrderShippingStates::STATE_READY, OrderPaymentStates::STATE_AWAITING_PAYMENT);

        $this->assertSame(WithdrawalPath::UNPAID_AUTOCANCEL, $this->checker(true)->resolvePath($order));
    }

    /** @test */
    function unpaid_not_shipped_order_is_not_withdrawable_when_the_flag_is_off()
    {
        $order = $this->order(OrderInterface::STATE_NEW, OrderShippingStates::STATE_READY, OrderPaymentStates::STATE_AWAITING_PAYMENT);

        $this->assertSame(WithdrawalPath::NONE, $this->checker(false)->resolvePath($order));
    }

    private function checker(bool $allowUnpaidWithdrawal): WithdrawalEligibilityChecker
    {
        return new WithdrawalEligibilityChecker($allowUnpaidWithdrawal);
    }

    private function order(string $state, string $shippingState, string $paymentState): OrderInterface
    {
        $order = $this->prophesize(OrderInterface::class);
        $order->getState()->willReturn($state);
        $order->getShippingState()->willReturn($shippingState);
        $order->getPaymentState()->willReturn($paymentState);

        return $order->reveal();
    }
}
