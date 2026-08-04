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

namespace Tests\Madcoders\SyliusRmaPlugin\Unit\Services\Pdf;

use Madcoders\SyliusRmaPlugin\Services\Pdf\GotenbergPdfGenerator;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Tests\Madcoders\SyliusRmaPlugin\Unit\UnitTestCase;

final class GotenbergPdfGeneratorTest extends UnitTestCase
{
    /** @test */
    public function it_posts_the_html_to_the_gotenberg_chromium_route_and_returns_the_pdf_bytes(): void
    {
        // given
        $httpClient = new MockHttpClient(function (string $method, string $url): MockResponse {
            self::assertSame('POST', $method);
            self::assertSame('http://gotenberg.test/forms/chromium/convert/html', $url);

            return new MockResponse('%PDF-1.4 fake', ['http_code' => 200]);
        });
        $generator = new GotenbergPdfGenerator($httpClient, 'http://gotenberg.test');

        // when
        $pdf = $generator->generateFromHtml('<html><body>Return form</body></html>');

        // then
        self::assertSame('%PDF-1.4 fake', $pdf);
    }

    /** @test */
    public function it_strips_a_trailing_slash_from_the_configured_gotenberg_url(): void
    {
        // given
        $httpClient = new MockHttpClient(function (string $method, string $url): MockResponse {
            self::assertSame('http://gotenberg.test/forms/chromium/convert/html', $url);

            return new MockResponse('%PDF-1.4 fake');
        });
        $generator = new GotenbergPdfGenerator($httpClient, 'http://gotenberg.test/');

        // when
        $generator->generateFromHtml('<html></html>');

        // then: no assertion failure raised inside the MockHttpClient callback above
        $this->addToAssertionCount(1);
    }
}
