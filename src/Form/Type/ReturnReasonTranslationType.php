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
use Symfony\Component\Validator\Constraints\NotBlank;

final class ReturnReasonTranslationType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'madcoders_rma.admin.reason.form.name',
                'constraints' => [
                    new NotBlank([
                        'message' => 'madcoders_rma.validator.name.not_blank',
                    ])
                ],
            ])
            ->add('slug', TextType::class, [
                'label' => 'madcoders_rma.admin.reason.form.slug', // TODO: consider changing to "code"
                'constraints' => [
                    new NotBlank([
                        'message' => 'madcoders_rma.validator.slug.not_blank',
                    ])
                ],
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'label' => 'madcoders_rma.admin.reason.form.description',
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'madcoders_rma_return_reason_translation';
    }
}
