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

use Madcoders\SyliusRmaPlugin\Entity\OrderReturnConsentInterface;
use Madcoders\SyliusRmaPlugin\Fixture\Factory\OrderReturnConsentFixtureFactory;
use Tests\Madcoders\SyliusRmaPlugin\Unit\UnitTestCase;

class OrderReturnConsentFixtureFactoryTest extends UnitTestCase
{
    /** @test */
    function it_creates_a_consent_without_a_description()
    {
        // description is optional (defaults to null); the fixture must still load
        $consent = (new OrderReturnConsentFixtureFactory())->create([
            'code' => 'consent_1',
            'name' => 'Consent 1',
            'slug' => 'consent_1',
        ]);

        $this->assertInstanceOf(OrderReturnConsentInterface::class, $consent);
        $this->assertNull($consent->getDescription());
    }

    /** @test */
    function it_creates_a_consent_with_a_description()
    {
        $consent = (new OrderReturnConsentFixtureFactory())->create([
            'code' => 'consent_1',
            'name' => 'Consent 1',
            'slug' => 'consent_1',
            'description' => 'Some description',
        ]);

        $this->assertSame('Some description', $consent->getDescription());
    }
}
