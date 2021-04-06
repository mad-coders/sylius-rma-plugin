<?php

declare(strict_types=1);

namespace Madcoders\SyliusRmaPlugin\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConsentFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('checked', CheckboxType::class, [
                    'label' => $options['consent_label'],
                    'required' => true]
            )
            ->add('label', HiddenType::class)
            ->add('code', HiddenType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('consent_label', null);
        $resolver->setRequired('consent_label');
    }

    public function getBlockPrefix(): string
    {
        return 'consent';
    }
}
