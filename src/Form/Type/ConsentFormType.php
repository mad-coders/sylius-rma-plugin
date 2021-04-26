<?php

declare(strict_types=1);

namespace Madcoders\SyliusRmaPlugin\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\IsTrue;

//use Symfony\Component\OptionsResolver\OptionsResolver;

class ConsentFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('label', HiddenType::class)
            ->add('code', HiddenType::class)
            ->add('consentRequire', HiddenType::class);

        $callback = function(FormEvent $event): void {
            $form = $event->getForm();
            $data = $event->getData();

            if (!is_array($data)) {
                throw new \RuntimeException('Consent data must be an array');
            }

            $constraints = [];
            if ($data['consentRequire']) {
                $constraints[] = new IsTrue();
            }

            $form->add('checked', CheckboxType::class, [
                    'label_attr' => ['style' => 'margin-bottom: 15px'],
                    'label' => (string) $data['label'] ?: '-- missing --',
                    'required' => (boolean) $data['consentRequire'] ?: false,
                    'constraints' => $constraints,
                ]
            );
        };

        $builder->addEventListener(FormEvents::PRE_SUBMIT, $callback);
        $builder->addEventListener(FormEvents::PRE_SET_DATA, $callback);
    }

    public function getBlockPrefix(): string
    {
        return 'consent';
    }
}
