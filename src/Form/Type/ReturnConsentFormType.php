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
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
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
//        $builder
//            ->add('consents', CollectionType::class, [
//                'label'    => '',
//                'required' => false
//            ])
//        ;

        $builder
            ->add('consents', ChoiceType::class, [
                'label'    => '',
                'required' => false,
                'expanded' => true,
                'choices' => $this->consentChoiceProvider->getChoices(),
            ])
            ->addModelTransformer(new CallbackTransformer(
                function(array $consents): array {
                    return $consents;
                },
                function(array $consents): array {
                    return $consents;
                }
            ));
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'madcoders_rma_return_consent';
    }
}
