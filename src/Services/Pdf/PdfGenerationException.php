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

use RuntimeException;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface as HttpClientExceptionInterface;

/**
 * Domain exception for PdfGeneratorInterface implementations, so callers depend on a stable
 * "PDF rendering failed" contract rather than a specific transport's exception hierarchy (e.g.
 * Symfony HttpClient's TransportExceptionInterface/HttpExceptionInterface for GotenbergPdfGenerator).
 */
final class PdfGenerationException extends RuntimeException
{
    public static function fromHttpClientFailure(string $serviceUrl, HttpClientExceptionInterface $previous): self
    {
        return new self(
            sprintf('Failed to render the PDF via "%s": %s', $serviceUrl, $previous->getMessage()),
            0,
            $previous,
        );
    }

    public static function fromUnexpectedContent(string $serviceUrl): self
    {
        return new self(
            sprintf('The PDF rendering service at "%s" returned a response that is not a PDF file.', $serviceUrl),
        );
    }
}
