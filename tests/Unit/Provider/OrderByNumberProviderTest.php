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

namespace Tests\Madcoders\SyliusRmaPlugin\Unit\Provider;

use Madcoders\SyliusRmaPlugin\Provider\OrderByNumberProvider;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Sylius\Component\Core\Model\Order;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Tests\Madcoders\SyliusRmaPlugin\Unit\UnitTestCase;

class OrderByNumberProviderTest extends UnitTestCase
{
    use ProphecyTrait;

    /** @test */
    function it_finds_order_with_prefix_when_prefix_given()
    {
        // given
        $repository = $this->getRepository([ '#00000123' => new Order() ]);
        $provider = new OrderByNumberProvider($repository);

        // when
        $result = $provider->findOneByNumber('#00000123');

        // then
        $this->assertInstanceOf(OrderInterface::class, $result);
    }

    /** @test */
    function it_finds_order_with_prefix_when_prefix_is_not_given()
    {
        // given
        $repository = $this->getRepository([ '#00000123' => new Order() ]);
        $provider = new OrderByNumberProvider($repository);

        // when
        $result = $provider->findOneByNumber('00000123');

        // then
        $this->assertInstanceOf(OrderInterface::class, $result);
    }

    /** @test */
    function it_finds_order_without_prefix_when_prefix_given()
    {
        // given
        $repository = $this->getRepository([ '00000123' => new Order() ]);
        $provider = new OrderByNumberProvider($repository);

        // when
        $result = $provider->findOneByNumber('#00000123');

        // then
        $this->assertInstanceOf(OrderInterface::class, $result);
    }

    /** @test */
    function it_finds_order_without_prefix_when_prefix_is_not_given()
    {
        // given
        $repository = $this->getRepository([ '00000123' => new Order() ]);
        $provider = new OrderByNumberProvider($repository);

        // when
        $result = $provider->findOneByNumber('00000123');

        // then
        $this->assertInstanceOf(OrderInterface::class, $result);
    }

    /** @test */
    function it_returns_null_if_order_not_found()
    {
        // given
        $repository = $this->getRepository();
        $provider = new OrderByNumberProvider($repository);

        // when
        $result = $provider->findOneByNumber('#00000123');

        // then
        $this->assertNull($result);
    }

    /** @test */
    function it_returns_null_when_order_is_not_found()
    {
        // given
        $repository = $this->getRepository([ '00000123' => new Order() ]);
        $provider = new OrderByNumberProvider($repository);

        // when
        $result = $provider->findOneByNumber('00000999');

        // then
        $this->assertNull($result);
    }

    /**
     * @param OrderInterface[] $testCases
     *
     * @return OrderRepositoryInterface
     */
    private function getRepository(array $testCases = []): OrderRepositoryInterface
    {
        $repository = $this->prophesize(OrderRepositoryInterface::class);
        $repository->findOneByNumber(Argument::any())->will(function (array $args) use ($testCases) {
            return $testCases[(string)($args[0] ?? '')] ?? null;
        });

        return $repository->reveal();
    }
}
