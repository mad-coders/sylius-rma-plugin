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

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        // A slug reused within a locale would otherwise hit the (locale, slug) unique index at flush
        // time as an HTTP 500. A consent without a slug stores NULL, which both this constraint and the
        // index ignore, so any number of inline consents can leave it empty (#69). Whether a slug is
        // required at all depends on the field type and is checked in OrderReturnConsentFormType.
        $resolver->setDefault('constraints', [
            new UniqueEntity([
                'fields' => ['locale', 'slug'],
                'errorPath' => 'slug',
                'message' => 'madcoders_rma.validator.slug.unique',
            ]),
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'madcoders_rma_return_consent_translation';
    }
}
