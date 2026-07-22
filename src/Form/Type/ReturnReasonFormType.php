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

use Madcoders\SyliusRmaPlugin\Entity\OrderReturnReasonInterface;
use Sylius\Bundle\ResourceBundle\Form\Type\ResourceTranslationsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\NotBlank;

final class ReturnReasonFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('enabled', CheckboxType::class, [
                'required' => false,
                'label' => 'madcoders_rma.admin.reasons.form.enabled',
            ])
            ->add('translations', ResourceTranslationsType::class, [
                'entry_type' => ReturnReasonTranslationType::class,
                'label' => 'madcoders_rma.admin.reasons.form.name',
            ])
            ->add('deadlineToReturn', IntegerType::class, [
                'required' => true,
                'label' => 'madcoders_rma.admin.reasons.form.days_to_deadline_to_return',
                'constraints' => [
                    new NotBlank([
                        'message' => 'madcoders_rma.validator.days_to_deadline_to_return.not_blank',
                    ]),
                ],
            ])
            ->add('position', IntegerType::class, [
                'required' => false,
                'label' => 'madcoders_rma.admin.reasons.form.position',
            ])
        ;

        // The code is immutable once the reason exists, so the field is only locked on the update
        // form. Sylius's AddCodeFormSubscriber cannot be used here: it locks the field whenever
        // getCode() is not null, and the entity initialises its code to an empty string, so the
        // field came out disabled and required at the same time on the create form.
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
            $reason = $event->getData();

            $event->getForm()->add('code', TextType::class, [
                'label' => 'sylius.ui.code',
                'required' => true,
                'disabled' => $reason instanceof OrderReturnReasonInterface && '' !== (string) $reason->getCode(),
                'constraints' => [
                    new NotBlank([
                        'message' => 'madcoders_rma.validator.code.not_blank',
                    ]),
                ],
            ]);
        });
    }

    public function getBlockPrefix(): string
    {
        return 'madcoders_rma_return_reason';
    }
}
