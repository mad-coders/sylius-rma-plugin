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

namespace Tests\Madcoders\SyliusRmaPlugin\Unit\Form\Type;

use Madcoders\SyliusRmaPlugin\Entity\OrderReturnItem;
use Madcoders\SyliusRmaPlugin\Form\Type\ReturnItemFormType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

class ReturnItemFormTypeTest extends TypeTestCase
{
    protected function getExtensions(): array
    {
        return [new ValidatorExtension(Validation::createValidator())];
    }

    /** @test */
    function a_blank_quantity_maps_to_zero_instead_of_crashing_the_int_setter()
    {
        // the form maps data into the entity before validation runs, so a null would reach the
        // int-typed OrderReturnItem::setReturnQty() and blow up with a TypeError (500)
        $item = $this->item(maxQty: 1, returnQty: 1);

        $form = $this->factory->create(ReturnItemFormType::class, $item);
        $form->submit(['itemToReturn' => '1', 'returnQty' => '']);

        self::assertTrue($form->isSynchronized());
        self::assertSame(0, $item->getReturnQty());
    }

    /** @test */
    function a_quantity_field_missing_from_the_submission_maps_to_zero()
    {
        // an item already returned in full has maxQty 0, its row is hidden in the form and its
        // quantity input may never reach the submitted payload - that must not be a 500 either
        $item = $this->item(maxQty: 0, returnQty: 0);

        $form = $this->factory->create(ReturnItemFormType::class, $item);
        $form->submit(['itemToReturn' => '0']);

        self::assertTrue($form->isSynchronized());
        self::assertSame(0, $item->getReturnQty());
    }

    /** @test */
    function a_quantity_above_the_returnable_maximum_is_rejected()
    {
        $item = $this->item(maxQty: 1, returnQty: 1);

        $form = $this->factory->create(ReturnItemFormType::class, $item);
        $form->submit(['itemToReturn' => '1', 'returnQty' => '3']);

        self::assertFalse($form->isValid());
    }

    /** @test */
    function a_negative_quantity_is_rejected()
    {
        $item = $this->item(maxQty: 2, returnQty: 1);

        $form = $this->factory->create(ReturnItemFormType::class, $item);
        $form->submit(['itemToReturn' => '1', 'returnQty' => '-1']);

        self::assertFalse($form->isValid());
    }

    /** @test */
    function a_valid_quantity_is_mapped_as_an_integer()
    {
        $item = $this->item(maxQty: 3, returnQty: 3);

        $form = $this->factory->create(ReturnItemFormType::class, $item);
        $form->submit(['itemToReturn' => '1', 'returnQty' => '2']);

        self::assertTrue($form->isValid());
        self::assertSame(2, $item->getReturnQty());
    }

    private function item(int $maxQty, int $returnQty): OrderReturnItem
    {
        $item = new OrderReturnItem();
        $item->setMaxQty($maxQty);
        $item->setReturnQty($returnQty);

        return $item;
    }
}
