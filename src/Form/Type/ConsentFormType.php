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
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\IsTrue;

class ConsentFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('label', HiddenType::class)
            ->add('code', HiddenType::class)
            ->add('consentRequire', HiddenType::class)
            ->add('fieldType', HiddenType::class)
            ->add('inlineHtml', HiddenType::class);

        $callback = function (FormEvent $event): void {
            $form = $event->getForm();
            $data = $event->getData();

            if (!is_array($data)) {
                throw new \RuntimeException('Consent data must be an array');
            }

            /** @var array<string, mixed> $normalized */
            $normalized = $data;
            $consentRequire = (bool) ($normalized['consentRequire'] ?? false);

            $constraints = [];
            if ($consentRequire) {
                $constraints[] = new IsTrue();
            }

            $form->add('checked', CheckboxType::class, $this->checkboxOptions($normalized, $consentRequire, $constraints));
        };

        $builder->addEventListener(FormEvents::PRE_SUBMIT, $callback);
        $builder->addEventListener(FormEvents::PRE_SET_DATA, $callback);
    }

    public function getBlockPrefix(): string
    {
        return 'consent';
    }

    /**
     * An inline consent renders its (admin-authored, trusted) description as HTML in the checkbox
     * label; every other consent keeps the plain-text name label. label_html is only enabled for
     * the inline case so the plain-text labels stay escaped as before.
     *
     * @param array<string, mixed> $data
     * @param object[] $constraints
     *
     * @return array<string, mixed>
     */
    private function checkboxOptions(array $data, bool $consentRequire, array $constraints): array
    {
        $isInline = OrderReturnConsentInterface::FIELD_TYPE_INLINE === ($data['fieldType'] ?? null);
        $inlineHtml = $data['inlineHtml'] ?? null;

        if ($isInline && is_scalar($inlineHtml) && '' !== (string) $inlineHtml) {
            return [
                'label_attr' => ['style' => 'margin-top: 7px'],
                'label' => (string) $inlineHtml,
                'label_html' => true,
                'required' => $consentRequire,
                'constraints' => $constraints,
            ];
        }

        $label = $data['label'] ?? null;
        $labelText = (is_scalar($label) && '' !== (string) $label) ? (string) $label : '-- missing --';

        return [
            'label_attr' => ['style' => 'margin-top: 7px'],
            'label' => $labelText,
            'required' => $consentRequire,
            'constraints' => $constraints,
        ];
    }
}
