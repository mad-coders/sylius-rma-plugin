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

use Madcoders\SyliusRmaPlugin\Entity\OrderReturnReasonGracePeriodInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Regex;

/**
 * Admin form granting (or changing) a grace period for one reason of an order (#67).
 *
 * The extra days field is a text field validated as digits only: an IntegerType would silently
 * round a value such as "10.5" instead of rejecting it.
 */
final class GracePeriodType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('reason', ChoiceType::class, [
                'label' => 'madcoders_rma.admin.grace_period.reason',
                'choices' => $options['reasons'],
                'placeholder' => 'madcoders_rma.admin.grace_period.choose_reason',
                'constraints' => [
                    new NotBlank(['message' => 'madcoders_rma.validator.grace_period.reason.not_blank']),
                ],
            ])
            ->add('extraDays', TextType::class, [
                'label' => 'madcoders_rma.admin.grace_period.extra_days',
                'attr' => [
                    'inputmode' => 'numeric',
                    'min' => OrderReturnReasonGracePeriodInterface::MIN_EXTRA_DAYS,
                    'max' => OrderReturnReasonGracePeriodInterface::MAX_EXTRA_DAYS,
                ],
                'constraints' => [
                    new NotBlank(['message' => 'madcoders_rma.validator.grace_period.extra_days.not_blank']),
                    new Regex([
                        'pattern' => '/^\d+$/',
                        'message' => 'madcoders_rma.validator.grace_period.extra_days.integer',
                    ]),
                    new Range([
                        'min' => OrderReturnReasonGracePeriodInterface::MIN_EXTRA_DAYS,
                        'max' => OrderReturnReasonGracePeriodInterface::MAX_EXTRA_DAYS,
                        'notInRangeMessage' => 'madcoders_rma.validator.grace_period.extra_days.range',
                    ]),
                ],
            ])
            ->add('note', TextareaType::class, [
                'label' => 'madcoders_rma.admin.grace_period.note',
                'required' => false,
                'attr' => ['rows' => 2],
                'constraints' => [
                    new Length(['max' => 1000]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('reasons')
            ->setAllowedTypes('reasons', 'array')
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'madcoders_rma_grace_period';
    }
}
