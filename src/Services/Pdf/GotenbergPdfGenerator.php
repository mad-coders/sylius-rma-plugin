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
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface as HttpClientExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Renders HTML to PDF via a Gotenberg instance's Chromium module
 * (https://gotenberg.dev/docs/routes#html-file-into-pdf-route), replacing the
 * wkhtmltopdf/knp_snappy binary dependency (see ADR 0013).
 */
final readonly class GotenbergPdfGenerator implements PdfGeneratorInterface
{
    /** A valid PDF file body starts with this header (ISO 32000-1 section 7.5.2). */
    private const PDF_MAGIC_BYTES = '%PDF-';

    public function __construct(
        private HttpClientInterface $httpClient,
        private string $gotenbergUrl,
        private float $timeout = 15.0,
        private float $maxDuration = 30.0,
    ) {
    }

    /**
     * @throws PdfGenerationException if Gotenberg is unreachable, responds with an error, or
     *                                 responds 200 with a body that is not a PDF
     */
    public function generateFromHtml(string $html): string
    {
        $formData = new FormDataPart([
            'files' => new DataPart($html, 'index.html', 'text/html'),
        ]);

        $endpoint = rtrim($this->gotenbergUrl, '/') . '/forms/chromium/convert/html';

        try {
            $response = $this->httpClient->request('POST', $endpoint, [
                'headers' => $formData->getPreparedHeaders()->toArray(),
                'body' => $formData->bodyToIterable(),
                'timeout' => $this->timeout,
                'max_duration' => $this->maxDuration,
            ]);

            $content = $response->getContent();
        } catch (HttpClientExceptionInterface $exception) {
            throw PdfGenerationException::fromHttpClientFailure($endpoint, $exception);
        }

        if (!str_starts_with($content, self::PDF_MAGIC_BYTES)) {
            throw PdfGenerationException::fromUnexpectedContent($endpoint);
        }

        return $content;
    }
}
