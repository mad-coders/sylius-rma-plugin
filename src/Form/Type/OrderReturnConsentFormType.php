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

use Madcoders\SyliusRmaPlugin\Entity\OrderReturnConsentInterface;
use Sylius\Bundle\ResourceBundle\Form\Type\ResourceTranslationsType;
use Sylius\Component\Resource\Translation\Provider\TranslationLocaleProviderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

final class OrderReturnConsentFormType extends AbstractType
{
    public function __construct(private readonly TranslationLocaleProviderInterface $localeProvider)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('enabled', CheckboxType::class, [
                'required' => false,
                'label' => 'madcoders_rma.admin.return_consent.form.enabled',
            ])
            ->add('translations', ResourceTranslationsType::class, [
                'entry_type' => OrderReturnConsentTranslationType::class,
                'label' => 'madcoders_rma.admin.return_consent.form.name',
            ])
            ->add('fieldType', ChoiceType::class, [
                'label' => 'madcoders_rma.admin.return_consent.form.field_type.label',
                'help' => 'madcoders_rma.admin.return_consent.form.field_type.help',
                'help_html' => true,
                'choices' => [
                    'madcoders_rma.admin.return_consent.form.field_type.external_page' => OrderReturnConsentInterface::FIELD_TYPE_EXTERNAL_PAGE,
                    'madcoders_rma.admin.return_consent.form.field_type.inline' => OrderReturnConsentInterface::FIELD_TYPE_INLINE,
                ],
            ])
            ->add('position', IntegerType::class, [
                'required' => false,
                'label' => 'madcoders_rma.admin.return_consent.form.position',
            ])
            ->add('consentRequire', CheckboxType::class, [
                'required' => false,
                'label' => 'madcoders_rma.admin.return_consent.form.require',
            ])
        ;

        // The code is immutable once the consent exists, so the field is only locked on the update
        // form. Sylius's AddCodeFormSubscriber cannot be used here: it locks the field whenever
        // getCode() is not null, and the entity initialises its code to an empty string, so the
        // field came out disabled on the create form (see the same fix for return reasons in #51).
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
            $consent = $event->getData();

            $event->getForm()->add('code', TextType::class, [
                'label' => 'sylius.ui.code',
                'required' => true,
                'disabled' => $consent instanceof OrderReturnConsentInterface && '' !== (string) $consent->getCode(),
                'constraints' => [
                    new NotBlank([
                        'message' => 'madcoders_rma.validator.code.not_blank',
                    ]),
                ],
            ]);
        });

        // The slug is only required for an external_page consent, and only on the default locale
        // (the other locales fall back to it). The requirement depends on the parent field type,
        // which the per-locale translation subform cannot see, so it is enforced here after submit.
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event): void {
            $consent = $event->getData();
            $form = $event->getForm();

            if (!$consent instanceof OrderReturnConsentInterface || $consent->isInline()) {
                return;
            }

            $defaultLocale = $this->localeProvider->getDefaultLocaleCode();

            if ('' !== trim((string) $consent->getTranslation($defaultLocale)->getSlug())) {
                return;
            }

            $slugField = $this->defaultLocaleSlugField($form, $defaultLocale);
            $slugField?->addError(new FormError('madcoders_rma.validator.slug.not_blank'));
        });
    }

    public function getBlockPrefix(): string
    {
        return 'madcoders_rma_return_consent';
    }

    private function defaultLocaleSlugField(FormInterface $form, string $defaultLocale): ?FormInterface
    {
        if (!$form->has('translations')) {
            return null;
        }

        $translations = $form->get('translations');
        if (!$translations->has($defaultLocale)) {
            return null;
        }

        $translation = $translations->get($defaultLocale);

        return $translation->has('slug') ? $translation->get('slug') : null;
    }
}
