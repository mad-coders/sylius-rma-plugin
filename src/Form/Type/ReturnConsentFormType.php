<?php
/*
 * This file is part of the Madcoders RMA Plugin.
 *
 * (c) Leonid Moshko
 *
 */
declare(strict_types=1);

namespace Madcoders\SyliusRmaPlugin\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ReturnConsentFormType extends AbstractType
{
    /** @var TranslatorInterface */
    private $translator;

    /**
     * ReturnConsentFormType constructor.
     * @param $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('orderReturnConsent', CheckboxType::class, [
                'label'    => 'madcoders_rma.ui.return_consent_text',
                'required' => true
            ])
            ->add('orderReturnConsentLabel', HiddenType::class, [
                'data' => $this->translator->trans('madcoders_rma.ui.return_consent_text'),
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'madcoders_rma_return_consent';
    }
}
