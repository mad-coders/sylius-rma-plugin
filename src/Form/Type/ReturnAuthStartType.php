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
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

final class ReturnAuthStartType extends AbstractType

{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('orderNumber', TextType::class, [
                'label'       => 'madcoders_rma.ui.return.enter_order_number',
                'required'    => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'madcoders_rma.order_number.not_blank',
                    ])
                ],
            ]);
    }

    public function getBlockPrefix(): string
    {
        return 'madcoders_rma_auth_start';
    }
}
