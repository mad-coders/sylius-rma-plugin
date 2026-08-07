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
use Sylius\Component\Resource\Translation\Provider\TranslationLocaleProviderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

final class ReturnReasonTranslationType extends AbstractResourceType
{
    /**
     * @param string $dataClass FQCN
     * @param string[] $validationGroups
     */
    public function __construct(
        string $dataClass,
        private readonly TranslationLocaleProviderInterface $localeProvider,
        array $validationGroups = [],
    ) {
        parent::__construct($dataClass, $validationGroups);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Only the default locale has to be filled in; the other locales are optional and fall back
        // to it. ResourceTranslationsType already renders the form that way - it marks only the
        // default locale's entry as required and drops an untouched entry before it is persisted -
        // so constraints on every entry rejected a form the UI presented as valid. The entry name
        // is the locale code (see ResourceTranslationsType::configureOptions).
        $isDefaultLocale = $builder->getName() === $this->localeProvider->getDefaultLocaleCode();

        $builder
            ->add('name', TextType::class, [
                'label' => 'madcoders_rma.admin.reasons.form.name',
                'required' => $isDefaultLocale,
                'constraints' => $isDefaultLocale ? [
                    new NotBlank(message: 'madcoders_rma.validator.name.not_blank'),
                ] : [],
            ])
            ->add('slug', TextType::class, [
                'label' => 'madcoders_rma.admin.reasons.form.slug',
                'required' => $isDefaultLocale,
                'constraints' => $isDefaultLocale ? [
                    new NotBlank(message: 'madcoders_rma.validator.slug.not_blank'),
                ] : [],
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'label' => 'madcoders_rma.admin.reasons.form.description',
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'madcoders_rma_return_reason_translation';
    }
}
