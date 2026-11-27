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

namespace Tests\Madcoders\SyliusRmaPlugin\Unit\Entity;

use Madcoders\SyliusRmaPlugin\Entity\OrderReturnConsentTranslation;
use Tests\Madcoders\SyliusRmaPlugin\Unit\UnitTestCase;

class OrderReturnConsentTranslationTest extends UnitTestCase
{
    /** @test */
    function a_new_translation_has_no_slug()
    {
        self::assertNull((new OrderReturnConsentTranslation())->getSlug());
    }

    /** @test */
    function a_missing_slug_is_stored_as_null()
    {
        // '' was stored before, and a second consent without a slug in a locale then hit the
        // (locale, slug) unique index as an HTTP 500 (#69); the index ignores NULLs
        foreach ([null, '', '   '] as $slug) {
            $translation = new OrderReturnConsentTranslation();
            $translation->setSlug($slug);

            self::assertNull($translation->getSlug(), sprintf('Expected %s to be stored as null.', var_export($slug, true)));
        }
    }

    /** @test */
    function a_filled_in_slug_is_kept()
    {
        $translation = new OrderReturnConsentTranslation();
        $translation->setSlug('terms');

        self::assertSame('terms', $translation->getSlug());
    }
}
