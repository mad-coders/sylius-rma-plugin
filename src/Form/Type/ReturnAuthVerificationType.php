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
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

final class ReturnAuthVerificationType extends AbstractType

{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('authCode', IntegerType::class, [
                'label'       => 'madcoders_rma.ui.verification_step.enter_verification_code',
                'required'    => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'madcoders_rma.not_blank',
                    ])
                ],
            ]);
    }

    public function getBlockPrefix(): string
    {
        return 'madcoders_rma_auth_verification';
    }
}
