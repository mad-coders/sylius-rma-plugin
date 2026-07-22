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

namespace Madcoders\SyliusRmaPlugin\Form\Type;

/**
 * The pre-shipment withdrawal reuses the standard return form (item selection, address, reason,
 * notes) so the customer sees the same screen. It is a distinct form type only so it can be wired
 * with the withdrawal-specific reason provider; it deliberately inherits
 * {@see ReturnFormType::getBlockPrefix()} so the shared template, form theme, and field names stay
 * identical between the return and withdrawal flows.
 *
 * The refund bank details are not added here: the flag-gated "Additional information" section in
 * {@see \Madcoders\SyliusRmaPlugin\Form\Extension\ReturnFormTypeExtension} covers this type too, so
 * the withdrawal form collects exactly what the return form collects.
 */
final class WithdrawalReturnFormType extends ReturnFormType
{
}
