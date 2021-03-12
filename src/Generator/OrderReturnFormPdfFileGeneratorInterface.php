<?php
/*
 * This file is part of the Madcoders RMA Plugin.
 *
 * (c) Leonid Moshko
 *
 */
declare(strict_types=1);

namespace Madcoders\SyliusRmaPlugin\Generator;

use Madcoders\SyliusRmaPlugin\Entity\OrderReturnInterface;
use Madcoders\SyliusRmaPlugin\Model\OrderReturnFormPdf;

interface OrderReturnFormPdfFileGeneratorInterface
{
    public function generate(OrderReturnInterface $orderReturnForm): OrderReturnFormPdf;
}
