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

    /** @test */
    function it_defaults_to_the_external_page_field_type()
    {
        $consent = (new OrderReturnConsentFixtureFactory())->create([
            'code' => 'consent_1',
            'name' => 'Consent 1',
            'slug' => 'consent_1',
        ]);

        $this->assertSame(OrderReturnConsentInterface::FIELD_TYPE_EXTERNAL_PAGE, $consent->getFieldType());
        $this->assertFalse($consent->isInline());
    }

    /** @test */
    function it_creates_an_inline_consent()
    {
        $consent = (new OrderReturnConsentFixtureFactory())->create([
            'code' => 'consent_1',
            'name' => 'Consent 1',
            'slug' => '',
            'field_type' => OrderReturnConsentInterface::FIELD_TYPE_INLINE,
        ]);

        $this->assertSame(OrderReturnConsentInterface::FIELD_TYPE_INLINE, $consent->getFieldType());
        $this->assertTrue($consent->isInline());
    }

    /** @test */
    function it_rejects_an_unknown_field_type()
    {
        $this->expectException(\Symfony\Component\OptionsResolver\Exception\InvalidOptionsException::class);

        (new OrderReturnConsentFixtureFactory())->create([
            'code' => 'consent_1',
            'name' => 'Consent 1',
            'slug' => 'consent_1',
            'field_type' => 'nonsense',
        ]);
    }
}
