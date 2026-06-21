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

namespace Madcoders\SyliusRmaPlugin\Services\AdditionalInformation;

interface AdditionalInformationCheckerInterface
{
    /**
     * Whether the "Additional information" section (bank account number, account holder name and
     * bank name / BIC-SWIFT) must be rendered on the return form and validated as required.
     */
    public function isRequired(): bool;
}
