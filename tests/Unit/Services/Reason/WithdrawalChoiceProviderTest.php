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

use Madcoders\SyliusRmaPlugin\Entity\OrderReturnInterface;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnReasonInterface;
use Madcoders\SyliusRmaPlugin\Services\Reason\WithdrawalChoiceProvider;
use Prophecy\PhpUnit\ProphecyTrait;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Tests\Madcoders\SyliusRmaPlugin\Unit\UnitTestCase;

class WithdrawalChoiceProviderTest extends UnitTestCase
{
    use ProphecyTrait;

    /** @test */
    function it_offers_every_enabled_reason_regardless_of_shipment_or_order_state()
    {
        // given a pre-shipment order (no shipment, no deadline to honour) and two enabled reasons
        $provider = $this->providerWithReasons([
            $this->reason('reason_14', 'Reason 14'),
            $this->reason('reason_360', 'Reason 360'),
        ]);

        // when
        $reasons = $provider->getChoices($this->prophesize(OrderReturnInterface::class)->reveal());

        // then both reasons are available - no shipment-deadline filtering applies to withdrawals
        $this->assertSame(['reason_14' => 'Reason 14', 'reason_360' => 'Reason 360'], $reasons);
    }

    /** @test */
    function it_skips_reasons_with_an_empty_code_or_name()
    {
        // given enabled reasons, one missing a code and one missing a name
        $provider = $this->providerWithReasons([
            $this->reason('reason_ok', 'Reason OK'),
            $this->reason(null, 'No Code'),
            $this->reason('reason_no_name', null),
        ]);

        // when
        $reasons = $provider->getChoices($this->prophesize(OrderReturnInterface::class)->reveal());

        // then only the well-formed reason survives
        $this->assertSame(['reason_ok' => 'Reason OK'], $reasons);
    }

    /** @test */
    function it_resolves_a_reason_name_by_code()
    {
        $reasonRepository = $this->prophesize(RepositoryInterface::class);
        $reasonRepository->findOneBy(['code' => 'reason_14'])->willReturn($this->reason('reason_14', 'Reason 14'));

        $provider = new WithdrawalChoiceProvider($reasonRepository->reveal());

        $this->assertSame('Reason 14', $provider->getNameByCode('reason_14'));
    }

    private function reason(?string $code, ?string $name): OrderReturnReasonInterface
    {
        $reason = $this->prophesize(OrderReturnReasonInterface::class);
        $reason->getCode()->willReturn($code);
        $reason->getName()->willReturn($name);

        return $reason->reveal();
    }

    /**
     * @param OrderReturnReasonInterface[] $reasons
     */
    private function providerWithReasons(array $reasons): WithdrawalChoiceProvider
    {
        $reasonRepository = $this->prophesize(RepositoryInterface::class);
        $reasonRepository->findBy(['enabled' => true])->willReturn($reasons);

        return new WithdrawalChoiceProvider($reasonRepository->reveal());
    }
}
