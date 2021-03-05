<?php
/*
 * This file is part of the Madcoders RMA Plugin.
 *
 * (c) Leonid Moshko
 *
 */
declare(strict_types=1);

namespace Madcoders\SyliusRmaPlugin\Form\Type;

use Madcoders\SyliusRmaPlugin\Entity\OrderReturn;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

final class ReturnFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('id', HiddenType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('items', CollectionType::class, [
                'entry_type' => ReturnItemFormType::class,
                'label'    => false,
                'required' => false,
                'entry_options' => ['label' => false ],

            ])
            ->add('returnReason', TextType::class, [
                'label'    => 'madcoders_rma.ui.return_reason',
                'required' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'madcoders_rma.order_number.not_blank',
                    ])
                ],
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class',  OrderReturn::class);
    }

    public function getBlockPrefix(): string
    {
        return 'madcoders_rma_return_item';
    }
}
