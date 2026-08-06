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
use Madcoders\SyliusRmaPlugin\Generator\OrderReturnFormPdfFileGenerator;
use Madcoders\SyliusRmaPlugin\Services\Configuration\ReturnAddressConfigurator;
use Madcoders\SyliusRmaPlugin\Services\Configuration\ReturnAddressData;
use Madcoders\SyliusRmaPlugin\Services\Pdf\PdfGeneratorInterface;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Config\FileLocatorInterface;
use Tests\Madcoders\SyliusRmaPlugin\Unit\UnitTestCase;
use Twig\Environment;

final class OrderReturnFormPdfFileGeneratorTest extends UnitTestCase
{
    use ProphecyTrait;

    /** @test */
    public function it_inlines_the_logo_as_a_data_uri_instead_of_an_absolute_filesystem_path(): void
    {
        // given: the logo path resolves to a real file on this host, as it would via file_locator
        $logoPath = __DIR__ . '/../../../src/Resources/pdf/sylius-logo.png';
        self::assertFileExists($logoPath);

        $fileLocator = $this->prophesize(FileLocatorInterface::class);
        $fileLocator->locate('@MadcodersSyliusRmaPlugin/Resources/pdf/sylius-logo.png')->willReturn($logoPath);

        $pdfGenerator = $this->prophesize(PdfGeneratorInterface::class);
        $pdfGenerator->generateFromHtml(Argument::any())->willReturn('%PDF-1.4 fake');

        $capturedContext = null;
        $templatingEngine = $this->prophesize(Environment::class);
        $templatingEngine->render(Argument::any(), Argument::any())
            ->will(function (array $args) use (&$capturedContext) {
                $capturedContext = $args[1];

                return '<html></html>';
            });

        $channel = $this->prophesize(ChannelInterface::class)->reveal();
        $channelsRepository = $this->prophesize(RepositoryInterface::class);
        $channelsRepository->findOneBy(['code' => 'WEB'])->willReturn($channel);

        $returnAddressConfigurator = $this->prophesize(ReturnAddressConfigurator::class);
        $returnAddressConfigurator->getReturnAddressForReturnForm($channel)
            ->willReturn(new ReturnAddressData('Madcoders', 'PL', 'Street 1', 'Warsaw', '00-001'));

        $orderReturnForm = $this->prophesize(OrderReturnInterface::class);
        $orderReturnForm->getChannelCode()->willReturn('WEB');
        $orderReturnForm->getReturnNumber()->willReturn('RMA-000123-1');

        $generator = new OrderReturnFormPdfFileGenerator(
            $templatingEngine->reveal(),
            $pdfGenerator->reveal(),
            $fileLocator->reveal(),
            '@MadcodersSyliusRmaPlugin/Return/Download/pdf.html.twig',
            '@MadcodersSyliusRmaPlugin/Resources/pdf/sylius-logo.png',
            $returnAddressConfigurator->reveal(),
            $channelsRepository->reveal(),
        );

        // when
        $generator->generate($orderReturnForm->reveal());

        // then: the template context carries an inlined data: URI...
        self::assertIsArray($capturedContext);
        self::assertArrayHasKey('orderReturnFormLogoDataUri', $capturedContext);
        self::assertStringStartsWith('data:image/png;base64,', $capturedContext['orderReturnFormLogoDataUri']);

        // ...and never the raw host filesystem path Gotenberg cannot resolve (regression for the
        // logo silently disappearing from every generated PDF, see PR #64 review).
        self::assertStringNotContainsString($logoPath, $capturedContext['orderReturnFormLogoDataUri']);
        foreach ($capturedContext as $value) {
            if (is_string($value)) {
                self::assertStringNotContainsString('file://', $value);
            }
        }
    }
}
