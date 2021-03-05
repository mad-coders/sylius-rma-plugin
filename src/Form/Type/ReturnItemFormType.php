<?php
/*
 * This file is part of the Madcoders RMA Plugin.
 *
 * (c) Leonid Moshko
 *
 */
declare(strict_types=1);

namespace Madcoders\SyliusRmaPlugin\Form\Type;

use Madcoders\SyliusRmaPlugin\Entity\OrderItemReturnRequest;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

final class ReturnItemFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('id', HiddenType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('itemToReturn', CheckboxType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('returnQty', NumberType::class, [
                'label'       => false,
                'required'    => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'madcoders_rma.return.not_blank',
                    ])
                ],
            ]);
    }


    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class',  OrderReturnItem::class);
    }

    public function getBlockPrefix(): string
    {
        return 'madcoders_rma_return_item';
    }
}
