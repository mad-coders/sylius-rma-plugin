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

use Symfony\Component\Form\FormBuilderInterface;

/**
 * The pre-shipment withdrawal reuses the standard return form (item selection, address, reason,
 * bank account, notes) so the customer sees the same screen. It is a distinct form type only so it
 * can be wired with the withdrawal-specific reason provider; it deliberately inherits
 * {@see ReturnFormType::getBlockPrefix()} so the shared template, form theme, and field names stay
 * identical between the return and withdrawal flows.
 */
final class WithdrawalReturnFormType extends ReturnFormType
{
    use AdditionalInformationFieldsTrait;

    /**
     * The withdrawal flow always collects the refund bank account, independently of the return
     * form's require_additional_information flag (changing the withdrawal flow is out of scope for
     * that flag). The {@see \Madcoders\SyliusRmaPlugin\Form\Extension\ReturnFormTypeExtension} that
     * carries the flag-gated section targets {@see ReturnFormType} only and does not reach this
     * subtype, so the bank account field is added here directly. The new account holder / bank name
     * fields are intentionally not collected on the withdrawal form.
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $this->addBankAccountField($builder);
    }
}
