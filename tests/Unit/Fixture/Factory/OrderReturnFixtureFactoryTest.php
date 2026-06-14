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

namespace Tests\Madcoders\SyliusRmaPlugin\Unit\Fixture\Factory;

use Madcoders\SyliusRmaPlugin\Entity\OrderReturnInterface;
use Madcoders\SyliusRmaPlugin\Fixture\Factory\OrderReturnFixtureFactory;
use Prophecy\PhpUnit\ProphecyTrait;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Tests\Madcoders\SyliusRmaPlugin\Unit\UnitTestCase;

class OrderReturnFixtureFactoryTest extends UnitTestCase
{
    use ProphecyTrait;

    /** @test */
    function it_creates_an_order_return_from_an_integer_customer_number()
    {
        // the shipped fixtures (and the documented option type) pass customer_number as an integer
        $orderReturn = $this->factoryWithChannel('FASHION_WEB')
            ->create($this->baseOptions() + ['customer_number' => 4]);

        $this->assertInstanceOf(OrderReturnInterface::class, $orderReturn);
        $this->assertSame('4', $orderReturn->getCustomerNumber());
    }

    /** @test */
    function it_accepts_a_string_customer_number()
    {
        $orderReturn = $this->factoryWithChannel('FASHION_WEB')
            ->create($this->baseOptions() + ['customer_number' => '4']);

        $this->assertSame('4', $orderReturn->getCustomerNumber());
    }

    /**
     * @return array<string, string>
     */
    private function baseOptions(): array
    {
        return [
            'channel_code' => 'FASHION_WEB',
            'order_number' => '000000001',
            'return_number' => 'RMA-000000001-1',
            'return_reason' => 'free-return-14',
        ];
    }

    private function factoryWithChannel(string $code): OrderReturnFixtureFactory
    {
        $channelRepository = $this->prophesize(ChannelRepositoryInterface::class);
        $channelRepository->findOneByCode($code)->willReturn($this->prophesize(ChannelInterface::class)->reveal());

        return new OrderReturnFixtureFactory($channelRepository->reveal());
    }
}
