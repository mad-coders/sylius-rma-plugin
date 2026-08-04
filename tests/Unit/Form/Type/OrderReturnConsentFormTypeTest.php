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

use Madcoders\SyliusRmaPlugin\Entity\OrderReturnConsent;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnConsentInterface;
use Madcoders\SyliusRmaPlugin\Form\Type\OrderReturnConsentFormType;
use Prophecy\PhpUnit\ProphecyTrait;
use Sylius\Resource\Translation\Provider\TranslationLocaleProviderInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Tests\Madcoders\SyliusRmaPlugin\Unit\UnitTestCase;

class OrderReturnConsentFormTypeTest extends UnitTestCase
{
    use ProphecyTrait;

    /** @test */
    function it_adds_a_field_type_choice_limited_to_the_two_field_types()
    {
        $options = null;

        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->method('add')->willReturnCallback(
            function (string $name, string $type, array $opts) use (&$options, $builder) {
                if ('fieldType' === $name) {
                    $options = $opts;
                }

                return $builder;
            },
        );
        $builder->method('addEventListener')->willReturnSelf();

        $this->formType()->buildForm($builder, []);

        self::assertIsArray($options, 'The form type did not add a "fieldType" field.');
        self::assertSame(
            [OrderReturnConsentInterface::FIELD_TYPE_EXTERNAL_PAGE, OrderReturnConsentInterface::FIELD_TYPE_INLINE],
            array_values($options['choices']),
        );
    }

    /** @test */
    function the_code_field_is_editable_while_creating_a_consent()
    {
        // OrderReturnConsent initialises its code to an empty string, which made Sylius's
        // AddCodeFormSubscriber lock the field on the create form (the same #51 defect)
        $options = $this->codeFieldOptionsFor(new OrderReturnConsent());

        self::assertFalse($options['disabled']);
    }

    /** @test */
    function the_code_field_is_locked_once_the_consent_has_a_code()
    {
        $consent = new OrderReturnConsent();
        $consent->setCode('consent_1');

        $options = $this->codeFieldOptionsFor($consent);

        self::assertTrue($options['disabled']);
    }

    private function formType(): OrderReturnConsentFormType
    {
        $localeProvider = $this->prophesize(TranslationLocaleProviderInterface::class);
        $localeProvider->getDefaultLocaleCode()->willReturn('en_US');

        return new OrderReturnConsentFormType($localeProvider->reveal());
    }

    /**
     * Builds the type, replays the PRE_SET_DATA listener with the given consent and returns the
     * options the "code" field was added with.
     */
    private function codeFieldOptionsFor(OrderReturnConsent $consent): array
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

        $this->formType()->buildForm($builder, []);

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
            $listener(new FormEvent($form, $consent));
        }

        self::assertIsArray($codeOptions, 'The form type did not add a "code" field.');

        return $codeOptions;
    }
}
