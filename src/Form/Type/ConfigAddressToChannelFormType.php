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

use Sylius\Bundle\AddressingBundle\Form\Type\CountryCodeChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

final class ConfigAddressToChannelFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('company', TextType::class, [
                'required' => false,
                'label' => 'sylius.form.address.company',
            ])
            ->add('countryCode', CountryCodeChoiceType::class, [
                'label' => 'sylius.form.address.country',
                'enabled' => true,
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'madcoders_rma.validator.not_blank',
                    ])
                ],
            ])
            ->add('street', TextType::class, [
                'label' => 'sylius.form.address.street',
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'madcoders_rma.validator.not_blank',
                    ])
                ],
            ])
            ->add('city', TextType::class, [
                'label' => 'sylius.form.address.city',
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'madcoders_rma.validator.not_blank',
                    ])
                ],
            ])
            ->add('postcode', TextType::class, [
                'label' => 'sylius.form.address.postcode',
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'madcoders_rma.validator.not_blank',
                    ])
                ],
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'madcoders_rma_config_address_to_channel';
    }
}
