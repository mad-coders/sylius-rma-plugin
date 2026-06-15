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

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Iban;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Shared "Additional information" field definitions so the field names, labels and validation stay
 * identical wherever they are used. Two callers add them on different terms:
 *  - {@see \Madcoders\SyliusRmaPlugin\Form\Extension\ReturnFormTypeExtension} adds the whole section
 *    (all three fields) to the standard return form, gated behind the require_additional_information
 *    flag.
 *  - {@see WithdrawalReturnFormType} always adds only the bank account field, independently of that
 *    flag, because the type extension targets {@see ReturnFormType} alone and does not reach the
 *    withdrawal subtype.
 */
trait AdditionalInformationFieldsTrait
{
    protected function addBankAccountField(FormBuilderInterface $builder): void
    {
        $builder->add('bankAccountNumber', TextType::class, [
            'label' => 'madcoders_rma.ui.form.bank_account_number',
            'required' => true,
            'constraints' => [
                new NotBlank([
                    'message' => 'madcoders_rma.validator.bank_account_number.not_blank',
                ]),
                new Iban([
                    'message' => 'madcoders_rma.validator.bank_account_number.not_a_valid',
                ]),
            ],
        ]);
    }

    protected function addAccountHolderNameField(FormBuilderInterface $builder): void
    {
        $builder->add('accountHolderName', TextType::class, [
            'label' => 'madcoders_rma.ui.form.account_holder_name',
            'required' => true,
            'constraints' => [
                new NotBlank([
                    'message' => 'madcoders_rma.validator.account_holder_name.not_blank',
                ]),
            ],
        ]);
    }

    protected function addBankNameField(FormBuilderInterface $builder): void
    {
        $builder->add('bankName', TextType::class, [
            'label' => 'madcoders_rma.ui.form.bank_name',
            'required' => true,
            'constraints' => [
                new NotBlank([
                    'message' => 'madcoders_rma.validator.bank_name.not_blank',
                ]),
            ],
        ]);
    }
}
