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

namespace Madcoders\SyliusRmaPlugin\Services\Pdf;

use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Renders HTML to PDF via a Gotenberg instance's Chromium module
 * (https://gotenberg.dev/docs/routes#html-file-into-pdf-route), replacing the
 * wkhtmltopdf/knp_snappy binary dependency (see ADR 0013).
 */
final readonly class GotenbergPdfGenerator implements PdfGeneratorInterface
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $gotenbergUrl,
    ) {
    }

    public function generateFromHtml(string $html): string
    {
        $formData = new FormDataPart([
            'files' => new DataPart($html, 'index.html', 'text/html'),
        ]);

        $response = $this->httpClient->request('POST', rtrim($this->gotenbergUrl, '/') . '/forms/chromium/convert/html', [
            'headers' => $formData->getPreparedHeaders()->toArray(),
            'body' => $formData->bodyToIterable(),
        ]);

        return $response->getContent();
    }
}
