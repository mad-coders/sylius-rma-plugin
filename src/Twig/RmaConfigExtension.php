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

use Madcoders\SyliusRmaPlugin\Services\AdditionalInformation\AdditionalInformationCheckerInterface;
use Twig\Attribute\AsTwigFunction;

final class RmaConfigExtension
{
    public function __construct(
        private readonly bool $returnFormPdfEnabled = false,
        private readonly bool $allowUnpaidWithdrawal = true,
        private readonly ?AdditionalInformationCheckerInterface $additionalInformationChecker = null,
    ) {
    }

    #[AsTwigFunction(name: 'madcoders_rma_return_form_pdf_enabled')]
    public function isReturnFormPdfEnabled(): bool
    {
        return $this->returnFormPdfEnabled;
    }

    #[AsTwigFunction(name: 'madcoders_rma_allow_unpaid_withdrawal')]
    public function isUnpaidWithdrawalAllowed(): bool
    {
        return $this->allowUnpaidWithdrawal;
    }

    #[AsTwigFunction(name: 'madcoders_rma_require_additional_information')]
    public function isAdditionalInformationRequired(): bool
    {
        return $this->additionalInformationChecker?->isRequired() ?? false;
    }
}
