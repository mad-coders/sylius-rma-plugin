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

namespace Tests\Madcoders\SyliusRmaPlugin\Unit\Generator;

use Madcoders\SyliusRmaPlugin\Entity\OrderReturnInterface;
use Madcoders\SyliusRmaPlugin\Generator\ReturnNumberGenerator;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Tests\Madcoders\SyliusRmaPlugin\Unit\UnitTestCase;

class ReturnNumberGeneratorTest extends UnitTestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy<RepositoryInterface<OrderReturnInterface>> */
    private ObjectProphecy $orderReturnRepository;

    protected function setUp(): void
    {
        $this->orderReturnRepository = $this->prophesize(RepositoryInterface::class);
    }

    /** @test */
    function it_generates_a_number_in_the_rma_order_sequence_format()
    {
        // no existing return numbers collide
        $this->orderReturnRepository->findOneBy(Argument::any())->willReturn(null);

        $this->assertSame('RMA-000123-1', $this->generator()->generate($this->order('000123')));
    }

    /** @test */
    function it_increments_the_sequence_until_the_number_is_unique()
    {
        // RMA-000123-1 already exists, RMA-000123-2 is free
        $this->orderReturnRepository->findOneBy(['returnNumber' => 'RMA-000123-1'])->willReturn($this->existingReturn());
        $this->orderReturnRepository->findOneBy(['returnNumber' => 'RMA-000123-2'])->willReturn(null);

        $this->assertSame('RMA-000123-2', $this->generator()->generate($this->order('000123')));
    }

    /** @test */
    function two_consecutive_returns_for_one_order_get_sequential_numbers()
    {
        $order = $this->order('000123');

        // first return: nothing exists yet
        $this->orderReturnRepository->findOneBy(['returnNumber' => 'RMA-000123-1'])->willReturn(null);
        $this->assertSame('RMA-000123-1', $this->generator()->generate($order));

        // second return: the first one now exists, so the next sequence is used
        $this->orderReturnRepository->findOneBy(['returnNumber' => 'RMA-000123-1'])->willReturn($this->existingReturn());
        $this->orderReturnRepository->findOneBy(['returnNumber' => 'RMA-000123-2'])->willReturn(null);
        $this->assertSame('RMA-000123-2', $this->generator()->generate($order));
    }

    /** @test */
    function the_deprecated_order_number_shim_produces_the_same_format()
    {
        $this->orderReturnRepository->findOneBy(Argument::any())->willReturn(null);

        $this->assertSame('RMA-000123-1', $this->generator()->returnNumberGenerate('000123'));
    }

    private function generator(): ReturnNumberGenerator
    {
        return new ReturnNumberGenerator($this->orderReturnRepository->reveal());
    }

    private function order(string $number): OrderInterface
    {
        $order = $this->prophesize(OrderInterface::class);
        $order->getNumber()->willReturn($number);

        return $order->reveal();
    }

    private function existingReturn(): OrderReturnInterface
    {
        return $this->prophesize(OrderReturnInterface::class)->reveal();
    }
}
