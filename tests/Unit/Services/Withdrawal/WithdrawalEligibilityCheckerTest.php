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
use Prophecy\PhpUnit\ProphecyTrait;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\OrderCheckoutStates;
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

        $this->assertFalse($this->checker(true)->isWithdrawable($order));
    }

    /** @test */
    function it_does_not_allow_withdrawal_for_a_partially_shipped_order()
    {
        $order = $this->order(OrderInterface::STATE_NEW, OrderShippingStates::STATE_PARTIALLY_SHIPPED, OrderPaymentStates::STATE_PAID);

        $this->assertFalse($this->checker(true)->isWithdrawable($order));
    }

    /** @test */
    function it_does_not_allow_withdrawal_for_a_cancelled_order()
    {
        $order = $this->order(OrderInterface::STATE_CANCELLED, OrderShippingStates::STATE_CANCELLED, OrderPaymentStates::STATE_CANCELLED);

        $this->assertFalse($this->checker(true)->isWithdrawable($order));
    }

    /** @test */
    function a_paid_not_shipped_order_is_withdrawable()
    {
        $order = $this->order(OrderInterface::STATE_NEW, OrderShippingStates::STATE_READY, OrderPaymentStates::STATE_PAID);

        $this->assertTrue($this->checker(true)->isWithdrawable($order));
    }

    /** @test */
    function an_authorized_not_shipped_order_is_withdrawable()
    {
        $order = $this->order(OrderInterface::STATE_NEW, OrderShippingStates::STATE_READY, OrderPaymentStates::STATE_AUTHORIZED);

        $this->assertTrue($this->checker(true)->isWithdrawable($order));
    }

    /** @test */
    function an_unpaid_not_shipped_order_is_withdrawable_when_the_flag_is_on()
    {
        $order = $this->order(OrderInterface::STATE_NEW, OrderShippingStates::STATE_READY, OrderPaymentStates::STATE_AWAITING_PAYMENT);

        $this->assertTrue($this->checker(true)->isWithdrawable($order));
    }

    /** @test */
    function an_unpaid_not_shipped_order_is_not_withdrawable_when_the_flag_is_off()
    {
        $order = $this->order(OrderInterface::STATE_NEW, OrderShippingStates::STATE_READY, OrderPaymentStates::STATE_AWAITING_PAYMENT);

        $this->assertFalse($this->checker(false)->isWithdrawable($order));
    }

    /** @test */
    function an_order_still_in_checkout_is_not_withdrawable()
    {
        // otherwise withdrawable (placed, not shipped, paid) but still a cart in checkout
        $order = $this->order(OrderInterface::STATE_NEW, OrderShippingStates::STATE_READY, OrderPaymentStates::STATE_PAID, OrderCheckoutStates::STATE_CART);

        $this->assertFalse($this->checker(true)->isWithdrawable($order));
    }

    private function checker(bool $allowUnpaidWithdrawal): WithdrawalEligibilityChecker
    {
        return new WithdrawalEligibilityChecker($allowUnpaidWithdrawal);
    }

    private function order(string $state, string $shippingState, string $paymentState, string $checkoutState = OrderCheckoutStates::STATE_COMPLETED): OrderInterface
    {
        $order = $this->prophesize(OrderInterface::class);
        $order->getState()->willReturn($state);
        $order->getShippingState()->willReturn($shippingState);
        $order->getPaymentState()->willReturn($paymentState);
        $order->getCheckoutState()->willReturn($checkoutState);

        return $order->reveal();
    }
}
