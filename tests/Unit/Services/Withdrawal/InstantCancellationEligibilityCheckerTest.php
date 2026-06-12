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

use Madcoders\SyliusRmaPlugin\Services\Withdrawal\InstantCancellationEligibilityChecker;
use Prophecy\PhpUnit\ProphecyTrait;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\OrderPaymentStates;
use Tests\Madcoders\SyliusRmaPlugin\Unit\UnitTestCase;

class InstantCancellationEligibilityCheckerTest extends UnitTestCase
{
    use ProphecyTrait;

    /** @test */
    function a_paid_order_is_not_instantly_withdrawable()
    {
        $this->assertFalse((new InstantCancellationEligibilityChecker())->isEligible($this->order(OrderPaymentStates::STATE_PAID)));
    }

    /** @test */
    function an_authorized_order_is_not_instantly_withdrawable()
    {
        $this->assertFalse((new InstantCancellationEligibilityChecker())->isEligible($this->order(OrderPaymentStates::STATE_AUTHORIZED)));
    }

    /** @test */
    function an_awaiting_payment_order_is_instantly_withdrawable()
    {
        $this->assertTrue((new InstantCancellationEligibilityChecker())->isEligible($this->order(OrderPaymentStates::STATE_AWAITING_PAYMENT)));
    }

    /** @test */
    function a_cart_payment_state_order_is_instantly_withdrawable()
    {
        $this->assertTrue((new InstantCancellationEligibilityChecker())->isEligible($this->order(OrderPaymentStates::STATE_CART)));
    }

    private function order(string $paymentState): OrderInterface
    {
        $order = $this->prophesize(OrderInterface::class);
        $order->getPaymentState()->willReturn($paymentState);

        return $order->reveal();
    }
}
