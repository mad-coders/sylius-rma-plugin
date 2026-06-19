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

namespace Madcoders\SyliusRmaPlugin\Form\Extension;

use Madcoders\SyliusRmaPlugin\Entity\NonReturnableProductInterface;
use Sylius\Bundle\ProductBundle\Form\Type\ProductType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Adds the "non-returnable" checkbox to the admin product form.
 *
 * The field is only added when the configured Product model implements
 * {@see NonReturnableProductInterface}; otherwise there is nothing to bind to and the form is left
 * untouched. Rendering of the field is wired through the sylius.admin.product.tab_details template
 * event (see Resources/config/config.yml).
 */
final class ProductTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $dataClass = $options['data_class'] ?? null;

        if (!is_string($dataClass) || !is_a($dataClass, NonReturnableProductInterface::class, true)) {
            return;
        }

        $builder->add('nonReturnable', CheckboxType::class, [
            'required' => false,
            'label' => 'madcoders_rma.form.product.non_returnable',
        ]);
    }

    public static function getExtendedTypes(): iterable
    {
        return [ProductType::class];
    }
}
