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

namespace Tests\Madcoders\SyliusRmaPlugin\Behat\Service;

use Madcoders\SyliusRmaPlugin\Services\Pdf\PdfGeneratorInterface;

/**
 * Stands in for the Gotenberg generator in the `test_pdf` environment, so Behat can follow the
 * return-form PDF path without a Gotenberg service. It keeps the HTML it was asked to render in a
 * file (the kernel may reboot between requests, so an in-memory property would not survive) and
 * returns a minimal PDF body. The Gotenberg HTTP request itself is covered by
 * GotenbergPdfGeneratorTest.
 */
final class RecordingPdfGenerator implements PdfGeneratorInterface
{
    public const PDF_BODY = "%PDF-1.4\n% madcoders rma behat\n%%EOF\n";

    public function __construct(private readonly string $recordFile)
    {
    }

    public function generateFromHtml(string $html): string
    {
        $directory = \dirname($this->recordFile);
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        file_put_contents($this->recordFile, $html);

        return self::PDF_BODY;
    }

    public function lastRenderedHtml(): ?string
    {
        if (!is_file($this->recordFile)) {
            return null;
        }

        $html = file_get_contents($this->recordFile);

        return false === $html ? null : $html;
    }

    public function reset(): void
    {
        if (is_file($this->recordFile)) {
            unlink($this->recordFile);
        }
    }
}
