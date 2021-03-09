<?php
/*
 * This file is part of the Madcoders RMA Plugin.
 *
 * (c) Leonid Moshko
 *
 */
declare(strict_types=1);

namespace Madcoders\SyliusRmaPlugin\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;

final class ReturnConsentFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('orderReturnConsent', CheckboxType::class, [
                'label'    => 'madcoders_rma.ui.return.consent_text',
                'required' => true
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'madcoders_rma_return_consent';
    }
}
