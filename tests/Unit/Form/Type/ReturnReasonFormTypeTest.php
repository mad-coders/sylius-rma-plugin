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

use Madcoders\SyliusRmaPlugin\Entity\OrderReturnReason;
use Madcoders\SyliusRmaPlugin\Form\Type\ReturnReasonFormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Tests\Madcoders\SyliusRmaPlugin\Unit\UnitTestCase;

class ReturnReasonFormTypeTest extends UnitTestCase
{
    /** @test */
    function the_code_field_is_editable_while_creating_a_reason()
    {
        // a brand new reason has an empty code, and locking the field there made the admin form
        // impossible to submit: the code was disabled and required at the same time
        $options = $this->codeFieldOptionsFor(new OrderReturnReason());

        self::assertFalse($options['disabled']);
    }

    /** @test */
    function the_code_field_is_locked_once_the_reason_has_a_code()
    {
        $reason = new OrderReturnReason();
        $reason->setCode('reason_360');

        $options = $this->codeFieldOptionsFor($reason);

        self::assertTrue($options['disabled']);
    }

    /**
     * Builds the type, replays the PRE_SET_DATA listener with the given reason and returns the
     * options the "code" field was added with.
     */
    private function codeFieldOptionsFor(OrderReturnReason $reason): array
    {
        $listeners = [];
        $codeOptions = null;

        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->method('add')->willReturnSelf();
        $builder->method('addEventListener')->willReturnCallback(
            function (string $event, callable $listener) use (&$listeners, $builder) {
                $listeners[$event][] = $listener;

                return $builder;
            },
        );

        (new ReturnReasonFormType())->buildForm($builder, []);

        $form = $this->createMock(FormInterface::class);
        $form->method('add')->willReturnCallback(
            function (string $name, string $type, array $options) use (&$codeOptions, $form) {
                if ('code' === $name) {
                    $codeOptions = $options;
                }

                return $form;
            },
        );

        foreach ($listeners[FormEvents::PRE_SET_DATA] ?? [] as $listener) {
            $listener(new FormEvent($form, $reason));
        }

        self::assertIsArray($codeOptions, 'The form type did not add a "code" field.');

        return $codeOptions;
    }
}
