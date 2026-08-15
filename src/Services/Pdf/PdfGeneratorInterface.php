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

interface PdfGeneratorInterface
{
    /**
     * Renders the given HTML document to a PDF and returns the raw file contents.
     *
     * @throws PdfGenerationException if rendering fails, e.g. the backing service is unreachable
     *                                 or returns a response that is not a PDF
     */
    public function generateFromHtml(string $html): string;
}
