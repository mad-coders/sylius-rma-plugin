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

namespace Tests\Madcoders\SyliusRmaPlugin\Unit\Services;

use Madcoders\SyliusRmaPlugin\Services\ReturnEligibilityChecker;
use Prophecy\PhpUnit\ProphecyTrait;
use Sylius\Component\Core\Model\OrderInterface;
use Tests\Madcoders\SyliusRmaPlugin\Unit\UnitTestCase;

class ReturnEligibilityCheckerTest extends UnitTestCase
{
    use ProphecyTrait;

    /** @test */
    function a_fulfilled_order_is_returnable()
    {
        $this->assertTrue((new ReturnEligibilityChecker())->isReturnable($this->order(OrderInterface::STATE_FULFILLED)));
    }

    /** @test */
    function a_placed_but_not_yet_fulfilled_order_is_not_returnable()
    {
        $this->assertFalse((new ReturnEligibilityChecker())->isReturnable($this->order(OrderInterface::STATE_NEW)));
    }

    /** @test */
    function a_cart_is_not_returnable()
    {
        $this->assertFalse((new ReturnEligibilityChecker())->isReturnable($this->order(OrderInterface::STATE_CART)));
    }

    /** @test */
    function a_cancelled_order_is_not_returnable()
    {
        $this->assertFalse((new ReturnEligibilityChecker())->isReturnable($this->order(OrderInterface::STATE_CANCELLED)));
    }

    private function order(string $state): OrderInterface
    {
        $order = $this->prophesize(OrderInterface::class);
        $order->getState()->willReturn($state);

        return $order->reveal();
    }
}
