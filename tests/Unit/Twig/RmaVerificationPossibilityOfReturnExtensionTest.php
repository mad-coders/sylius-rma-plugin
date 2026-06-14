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

namespace Tests\Madcoders\SyliusRmaPlugin\Unit\Twig;

use Madcoders\SyliusRmaPlugin\Services\RmaVerificationPossibilityOfReturn;
use Madcoders\SyliusRmaPlugin\Services\Withdrawal\WithdrawalEligibilityCheckerInterface;
use Madcoders\SyliusRmaPlugin\Twig\RmaVerificationPossibilityOfReturnExtension;
use Prophecy\PhpUnit\ProphecyTrait;
use Sylius\Component\Core\Model\OrderInterface;
use Tests\Madcoders\SyliusRmaPlugin\Unit\UnitTestCase;

class RmaVerificationPossibilityOfReturnExtensionTest extends UnitTestCase
{
    use ProphecyTrait;

    /** @test */
    function a_withdrawable_order_can_start_an_rma()
    {
        // given a withdrawable (pre-shipment) order
        $extension = $this->extension(isWithdrawable: true, hasItemsToReturn: false);

        // then the RMA process is offered without checking the return path
        $this->assertTrue($extension->canStartRma($this->prophesize(OrderInterface::class)->reveal()));
    }

    /** @test */
    function a_returnable_order_with_items_can_start_an_rma()
    {
        // given a non-withdrawable order that still has items to return
        $extension = $this->extension(isWithdrawable: false, hasItemsToReturn: true);

        // then the RMA process is offered
        $this->assertTrue($extension->canStartRma($this->prophesize(OrderInterface::class)->reveal()));
    }

    /** @test */
    function an_order_that_is_neither_withdrawable_nor_returnable_cannot_start_an_rma()
    {
        // given an order that is neither withdrawable nor has anything left to return
        $extension = $this->extension(isWithdrawable: false, hasItemsToReturn: false);

        // then no RMA process is offered
        $this->assertFalse($extension->canStartRma($this->prophesize(OrderInterface::class)->reveal()));
    }

    private function extension(bool $isWithdrawable, bool $hasItemsToReturn): RmaVerificationPossibilityOfReturnExtension
    {
        $withdrawalEligibilityChecker = $this->prophesize(WithdrawalEligibilityCheckerInterface::class);
        $withdrawalEligibilityChecker->isWithdrawable(\Prophecy\Argument::any())->willReturn($isWithdrawable);

        $verificationPossibilityOfReturn = $this->prophesize(RmaVerificationPossibilityOfReturn::class);
        $verificationPossibilityOfReturn->verificationForButtonRender(\Prophecy\Argument::any())->willReturn($hasItemsToReturn);

        return new RmaVerificationPossibilityOfReturnExtension(
            $verificationPossibilityOfReturn->reveal(),
            $withdrawalEligibilityChecker->reveal(),
        );
    }
}
