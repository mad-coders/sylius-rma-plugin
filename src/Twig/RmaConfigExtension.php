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

namespace Madcoders\SyliusRmaPlugin\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class RmaConfigExtension extends AbstractExtension
{
    public function __construct(
        private readonly bool $returnFormPdfEnabled = false,
        private readonly bool $allowUnpaidWithdrawal = true,
    ) {
    }

    /** @inheritdoc */
    public function getFunctions()
    {
        return [
            new TwigFunction('madcoders_rma_return_form_pdf_enabled', $this->isReturnFormPdfEnabled(...)),
            new TwigFunction('madcoders_rma_allow_unpaid_withdrawal', $this->isUnpaidWithdrawalAllowed(...)),
        ];
    }

    public function isReturnFormPdfEnabled(): bool
    {
        return $this->returnFormPdfEnabled;
    }

    public function isUnpaidWithdrawalAllowed(): bool
    {
        return $this->allowUnpaidWithdrawal;
    }
}
