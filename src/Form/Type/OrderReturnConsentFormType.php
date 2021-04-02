<?php
/*
 * This file is part of the Madcoders RMA Plugin.
 *
 * (c) Leonid Moshko
 *
 */
declare(strict_types=1);

namespace Madcoders\SyliusRmaPlugin\Form\Type;

use Sylius\Bundle\ResourceBundle\Form\EventSubscriber\AddCodeFormSubscriber;
use Sylius\Bundle\ResourceBundle\Form\Type\ResourceTranslationsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;

final class OrderReturnConsentFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void {
        $builder
            ->add('enabled', CheckboxType::class, [
                'required' => false,
                'label' => 'madcoders_rma.form.return_consent.enabled',
            ])
            ->add('translations', ResourceTranslationsType::class, [
                'entry_type' => OrderReturnConsentTranslationType::class,
                'label' => 'madcoders_rma.ui.form.return_consent.name',
            ])
            ->add('position', IntegerType::class, [
                'required' => false,
                'label' => 'madcoders_rma.form.return_consent.position',
            ])
            ->addEventSubscriber(new AddCodeFormSubscriber())
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'madcoders_rma_return_consent';
    }
}
