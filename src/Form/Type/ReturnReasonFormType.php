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
use Symfony\Component\Validator\Constraints\NotBlank;

final class ReturnReasonFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void {
        $builder
            ->add('enabled', CheckboxType::class, [
                'required' => false,
                'label' => 'madcoders_rma.form.return_reason.enabled',
            ])
            ->add('translations', ResourceTranslationsType::class, [
                'entry_type' => ReturnReasonTranslationType::class,
                'label' => 'madcoders_rma.ui.form.return_reason.name',
            ])
            ->add('deadlineToReturn', IntegerType::class, [
                'required' => true,
                'label' => 'madcoders_rma.form.return_reason.days_to_deadline_to_return',
                'constraints' => [
                    new NotBlank([
                        'message' => 'madcoders_rma.validator.days_to_deadline_to_return.not_blank',
                    ])
                ],
            ])
            ->add('position', IntegerType::class, [
                'required' => false,
                'label' => 'madcoders_rma.form.return_reason.position',
            ])
            ->addEventSubscriber(new AddCodeFormSubscriber())
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'madcoders_rma_return_reason';
    }
}
