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

namespace Madcoders\SyliusRmaPlugin\Form\Extension;

use Madcoders\SyliusRmaPlugin\Form\Type\AdditionalInformationFieldsTrait;
use Madcoders\SyliusRmaPlugin\Form\Type\ReturnFormType;
use Madcoders\SyliusRmaPlugin\Services\AdditionalInformation\AdditionalInformationCheckerInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Adds the optional "Additional information" section (bank account number, account holder name and
 * bank name / BIC-SWIFT) to the standard return form. The whole section is gated behind the
 * require_additional_information flag: when it is off (the default) no fields are added, so the
 * return form stays lightweight and submission is not blocked; when it is on the three fields are
 * added and validated as required.
 *
 * It is a form type extension rather than fields baked into {@see ReturnFormType} so the feature is
 * self-contained and can be enabled, tested or removed in isolation. Symfony resolves type
 * extensions by exact type name, so this extension applies only to the standard return form and not
 * to {@see \Madcoders\SyliusRmaPlugin\Form\Type\WithdrawalReturnFormType}, which collects the bank
 * account on its own, flag-independent terms.
 */
final class ReturnFormTypeExtension extends AbstractTypeExtension
{
    use AdditionalInformationFieldsTrait;

    public function __construct(
        private readonly AdditionalInformationCheckerInterface $additionalInformationChecker,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (!$this->additionalInformationChecker->isRequired()) {
            return;
        }

        $this->addBankAccountField($builder);
        $this->addAccountHolderNameField($builder);
        $this->addBankNameField($builder);
    }

    public static function getExtendedTypes(): iterable
    {
        return [ReturnFormType::class];
    }
}
