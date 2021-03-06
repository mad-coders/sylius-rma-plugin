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

use Madcoders\SyliusRmaPlugin\Entity\OrderReturn;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnInterface;
use Madcoders\SyliusRmaPlugin\Services\Reason\ChoiceProviderInterface;
use Sylius\Bundle\AddressingBundle\Form\Type\CountryCodeChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Iban;
use Symfony\Component\Validator\Constraints\NotBlank;

final class ReturnFormType extends AbstractType
{
    /** @var ChoiceProviderInterface */
    private $reasonChoiceProvider;

    public function __construct(ChoiceProviderInterface $reasonChoiceProvider)
    {
        $this->reasonChoiceProvider = $reasonChoiceProvider;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('items', CollectionType::class, [
                'entry_type' => ReturnItemFormType::class,
                'label'    => false,
                'required' => false,
                'entry_options' => ['label' => false ],

            ])
            ->add('firstName', TextType::class, [
                'label' => 'sylius.form.address.first_name',
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'madcoders_rma.validator.not_blank',
                    ])
                ],
            ])
            ->add('lastName', TextType::class, [
                'label' => 'sylius.form.address.last_name',
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'madcoders_rma.validator.not_blank',
                    ])
                ],
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
            ->add('customerNote', TextareaType::class, [
                'attr'        => ['rows' => '2'],
                'required' => false,
                'label' => 'madcoders_rma.ui.add_notes',
            ])
            ->add('bankAccountNumber', TextType::class, [
                'label' => 'madcoders_rma.ui.form.bank_account_number',
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

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
            $orderReturn = $event->getData();
            $form = $event->getForm();

            if (!$orderReturn instanceof OrderReturnInterface) {
                throw new \InvalidArgumentException(sprintf('$orderReturn must implement %s interface', OrderReturnInterface::class));
            }

            $choices = $this->reasonChoiceProvider->getChoices($orderReturn);

            $form->add('returnReason', ChoiceType::class, [
                'label'    => 'madcoders_rma.ui.form.return_reason',
                'required' => true,
                'placeholder' => 'madcoders_rma.ui.form.placeholder.reason',
                'empty_data' => '',
                'choices' => array_flip($choices),
                'constraints' => [
                    new NotBlank([
                        'message' => 'madcoders_rma.validator.not_blank',
                    ])
                ],
            ]);
        });
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
