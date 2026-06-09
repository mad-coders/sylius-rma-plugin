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

use Madcoders\SyliusRmaPlugin\Entity\OrderReturnReasonInterface;
use Madcoders\SyliusRmaPlugin\Fixture\Factory\OrderReturnReasonFixtureFactory;
use Tests\Madcoders\SyliusRmaPlugin\Unit\UnitTestCase;

class OrderReturnReasonFixtureFactoryTest extends UnitTestCase
{
    /** @test */
    function it_creates_a_reason_without_a_description()
    {
        // description is optional (defaults to null); the fixture must still load
        $reason = (new OrderReturnReasonFixtureFactory())->create([
            'code' => 'reason_1',
            'name' => 'Reason 1',
            'slug' => 'reason_1',
            'deadline_to_return' => 14,
        ]);

        $this->assertInstanceOf(OrderReturnReasonInterface::class, $reason);
        $this->assertNull($reason->getDescription());
    }

    /** @test */
    function it_creates_a_reason_with_a_description()
    {
        $reason = (new OrderReturnReasonFixtureFactory())->create([
            'code' => 'reason_1',
            'name' => 'Reason 1',
            'slug' => 'reason_1',
            'deadline_to_return' => 14,
            'description' => 'Some description',
        ]);

        $this->assertSame('Some description', $reason->getDescription());
    }
}
