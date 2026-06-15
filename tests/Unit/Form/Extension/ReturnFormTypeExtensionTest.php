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

namespace Tests\Madcoders\SyliusRmaPlugin\Unit\Form\Extension;

use Madcoders\SyliusRmaPlugin\Form\Extension\ReturnFormTypeExtension;
use Madcoders\SyliusRmaPlugin\Form\Type\ReturnFormType;
use Madcoders\SyliusRmaPlugin\Services\AdditionalInformation\AdditionalInformationChecker;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Iban;
use Symfony\Component\Validator\Constraints\NotBlank;
use Tests\Madcoders\SyliusRmaPlugin\Unit\UnitTestCase;

class ReturnFormTypeExtensionTest extends UnitTestCase
{
    use ProphecyTrait;

    /** @test */
    function it_extends_the_return_form_type()
    {
        // the gated section attaches to the standard return form only, not to its subtypes
        $this->assertSame([ReturnFormType::class], iterator_to_array((function () {
            yield from ReturnFormTypeExtension::getExtendedTypes();
        })()));
    }

    /** @test */
    function it_adds_the_required_additional_information_fields_when_the_flag_is_on()
    {
        // given the additional-information flag is enabled
        $builder = $this->prophesize(FormBuilderInterface::class);
        $double = $builder->reveal();
        $builder->add(Argument::cetera())->willReturn($double);

        // when the form is built
        $this->extension(required: true)->buildForm($double, []);

        // then all three fields are added with their validation constraints
        $builder->add('bankAccountNumber', Argument::any(), Argument::that($this->hasConstraints(NotBlank::class, Iban::class)))
            ->shouldHaveBeenCalled();
        $builder->add('accountHolderName', Argument::any(), Argument::that($this->hasConstraints(NotBlank::class)))
            ->shouldHaveBeenCalled();
        $builder->add('bankName', Argument::any(), Argument::that($this->hasConstraints(NotBlank::class)))
            ->shouldHaveBeenCalled();
    }

    /** @test */
    function it_does_not_add_any_additional_information_field_when_the_flag_is_off()
    {
        // given the additional-information flag is disabled
        $builder = $this->prophesize(FormBuilderInterface::class);
        $double = $builder->reveal();
        $builder->add(Argument::cetera())->willReturn($double);

        // when the form is built
        $this->extension(required: false)->buildForm($double, []);

        // then none of the additional-information fields are added, so submission is not blocked
        $builder->add('bankAccountNumber', Argument::cetera())->shouldNotHaveBeenCalled();
        $builder->add('accountHolderName', Argument::cetera())->shouldNotHaveBeenCalled();
        $builder->add('bankName', Argument::cetera())->shouldNotHaveBeenCalled();
    }

    private function extension(bool $required): ReturnFormTypeExtension
    {
        return new ReturnFormTypeExtension(new AdditionalInformationChecker($required));
    }

    /**
     * Asserts the field options carry exactly the given constraint classes.
     *
     * @param class-string ...$expected
     */
    private function hasConstraints(string ...$expected): callable
    {
        return static function (array $options) use ($expected): bool {
            $constraints = $options['constraints'] ?? [];
            $actual = array_map(static fn (object $constraint): string => $constraint::class, $constraints);
            sort($actual);
            sort($expected);

            return $actual === $expected;
        };
    }
}
