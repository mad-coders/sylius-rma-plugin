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
                        'message' => 'madcoders_rma.validator.not_blank',
                    ])
                ],
            ]);
    }

    public function getBlockPrefix(): string
    {
        return 'madcoders_rma_auth_verification';
    }
}
