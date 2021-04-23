<?php
/*
 * This file is part of the Madcoders RMA Plugin.
 *
 * (c) Leonid Moshko
 *
 */
declare(strict_types=1);

namespace Madcoders\SyliusRmaPlugin\Form\Type;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class OrderReturnConsentTranslationType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'madcoders_rma.admin.return_consent.form.name',
            ])
            ->add('slug', TextType::class, [
                'label' => 'madcoders_rma.admin.return_consent.form.slug',
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'label' => 'madcoders_rma.admin.return_consent.form.description',
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'madcoders_rma_return_consent_translation';
    }
}
