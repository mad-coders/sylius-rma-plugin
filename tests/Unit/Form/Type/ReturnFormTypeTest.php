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

use Madcoders\SyliusRmaPlugin\Form\Type\ReturnFormType;
use Madcoders\SyliusRmaPlugin\Form\Type\WithdrawalReturnFormType;
use Madcoders\SyliusRmaPlugin\Services\AdditionalInformation\AdditionalInformationChecker;
use Madcoders\SyliusRmaPlugin\Services\Reason\ChoiceProviderInterface;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Iban;
use Symfony\Component\Validator\Constraints\NotBlank;
use Tests\Madcoders\SyliusRmaPlugin\Unit\UnitTestCase;

class ReturnFormTypeTest extends UnitTestCase
{
    use ProphecyTrait;

    /** @test */
    function it_adds_the_required_additional_information_fields_when_the_flag_is_on()
    {
        // given the additional-information flag is enabled
        $builder = $this->prophesize(FormBuilderInterface::class);
        $double = $builder->reveal();
        $builder->add(Argument::cetera())->willReturn($double);
        $builder->addEventListener(Argument::cetera())->willReturn($double);

        // when the form is built
        $this->formType(required: true)->buildForm($double, []);

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
        $builder->addEventListener(Argument::cetera())->willReturn($double);

        // when the form is built
        $this->formType(required: false)->buildForm($double, []);

        // then none of the additional-information fields are added, so submission is not blocked
        $builder->add('bankAccountNumber', Argument::cetera())->shouldNotHaveBeenCalled();
        $builder->add('accountHolderName', Argument::cetera())->shouldNotHaveBeenCalled();
        $builder->add('bankName', Argument::cetera())->shouldNotHaveBeenCalled();
    }

    /** @test */
    function the_withdrawal_form_always_keeps_the_bank_account_field_regardless_of_the_flag()
    {
        // given the additional-information flag is disabled
        $builder = $this->prophesize(FormBuilderInterface::class);
        $double = $builder->reveal();
        $builder->add(Argument::cetera())->willReturn($double);
        $builder->addEventListener(Argument::cetera())->willReturn($double);

        // when the withdrawal form (a subclass of the return form) is built
        $withdrawalForm = new WithdrawalReturnFormType(
            $this->prophesize(ChoiceProviderInterface::class)->reveal(),
            new AdditionalInformationChecker(false),
        );
        $withdrawalForm->buildForm($double, []);

        // then the bank account field stays required, but the new fields are not added
        $builder->add('bankAccountNumber', Argument::any(), Argument::that($this->hasConstraints(NotBlank::class, Iban::class)))
            ->shouldHaveBeenCalled();
        $builder->add('accountHolderName', Argument::cetera())->shouldNotHaveBeenCalled();
        $builder->add('bankName', Argument::cetera())->shouldNotHaveBeenCalled();
    }

    private function formType(bool $required): ReturnFormType
    {
        return new ReturnFormType(
            $this->prophesize(ChoiceProviderInterface::class)->reveal(),
            new AdditionalInformationChecker($required),
        );
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
