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
use Sylius\Bundle\AddressingBundle\Form\Type\CountryCodeChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Iban;
use Symfony\Component\Validator\Constraints\NotBlank;

final class ReturnFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('items', CollectionType::class, [
                'entry_type' => ReturnItemFormType::class,
                'label'    => false,
                'required' => false,
                'entry_options' => ['label' => false ],

            ])
            ->add('returnReason', TextType::class, [
                'label'    => 'madcoders_rma.ui.return_reason',
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'madcoders_rma.validator.order_number.not_blank',
                    ])
                ],
            ])
            ->add('firstName', TextType::class, [
                'label' => 'sylius.form.address.first_name',
            ])
            ->add('lastName', TextType::class, [
                'label' => 'sylius.form.address.last_name',
            ])
            ->add('phoneNumber', TextType::class, [
                'required' => false,
                'label' => 'sylius.form.address.phone_number',
            ])
            ->add('customerEmail', TextType::class, [
                'required' => true,
                'label' => 'sylius.ui.email',
            ])
            ->add('company', TextType::class, [
                'required' => false,
                'label' => 'sylius.form.address.company',
            ])
            ->add('countryCode', CountryCodeChoiceType::class, [
                'label' => 'sylius.form.address.country',
                'enabled' => true,
            ])
            ->add('street', TextType::class, [
                'label' => 'sylius.form.address.street',
            ])
            ->add('city', TextType::class, [
                'label' => 'sylius.form.address.city',
            ])
            ->add('postcode', TextType::class, [
                'label' => 'sylius.form.address.postcode',
            ])
            ->add('provinceName', TextType::class, [
                'label' => 'sylius.form.province.name',
            ])
            ->add('bankAccountNumber', TextType::class, [
                'label' => 'madcoders_rma.ui.return.bank_account_number',
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'madcoders_rma.validator.bank_account_number.not_blank',
                    ]),
                    new Iban([
                        'message' => 'madcoders_rma.validator.bank_account_number.not_a_valid',
                    ])
                ],
            ])
        ;
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
