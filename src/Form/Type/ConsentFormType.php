<?php

declare(strict_types=1);

namespace Madcoders\SyliusRmaPlugin\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
//use Symfony\Component\OptionsResolver\OptionsResolver;

class ConsentFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('label', HiddenType::class)
            ->add('code', HiddenType::class);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();

            if (!is_array($data)) {
                throw new \RuntimeException('Consent data must be an array');
            }

            $form->add('checked', CheckboxType::class, [
                    'label' => (string) $data['label'] ?: '-- missing --',
                    'required' => true
                ]
            );
        });
    }

    public function getBlockPrefix(): string
    {
        return 'consent';
    }
}
