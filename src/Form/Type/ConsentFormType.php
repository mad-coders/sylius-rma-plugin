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
                    'label_attr' => ['style' => 'margin-top: 7px'],
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
