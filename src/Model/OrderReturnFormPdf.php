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

namespace Madcoders\SyliusRmaPlugin\Model;

final readonly class OrderReturnFormPdf
{
    public function __construct(private string $filename, private string $content)
    {
    }

    public function filename(): string
    {
        return $this->filename;
    }

    public function content(): string
    {
        return $this->content;
    }
}
