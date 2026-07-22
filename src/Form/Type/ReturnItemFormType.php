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

use Madcoders\SyliusRmaPlugin\Entity\OrderReturnItem;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnItemInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;

final class ReturnItemFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('itemToReturn', CheckboxType::class, [
                'label' => false,
                'required' => false,
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
            $returnItem = $event->getData();
            $form = $event->getForm();

            if (!$returnItem instanceof OrderReturnItemInterface) {
                return;
            }

            // IntegerType matches the int property/column, and empty_data keeps a blank or missing
            // input mapping to 0 ("nothing returned") instead of null - the form maps data into the
            // entity before validation runs, so a null would hit the int-typed setter as a TypeError.
            $form->add('returnQty', IntegerType::class, [
                'attr' => ['style' => 'max-width: 200px; display: block;', 'min' => 0],
                'label' => false,
                'required' => true,
                'empty_data' => '0',
                'constraints' => [
                    new NotBlank([
                        'message' => 'madcoders_rma.validator.not_blank',
                    ]),
                    new GreaterThanOrEqual([
                        'value' => 0,
                        'message' => 'madcoders_rma.validator.return_qty_greater_or_equal',
                    ]),
                    new LessThanOrEqual([
                        'value' => $returnItem->getMaxQty(),
                        'message' => 'madcoders_rma.validator.return_qty_less_or_equal',
                    ]),
                ],
            ]);
        });
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', OrderReturnItem::class);
    }

    public function getBlockPrefix(): string
    {
        return 'madcoders_rma_return_item';
    }
}
