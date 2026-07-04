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
use Madcoders\SyliusRmaPlugin\Services\Reason\ChoiceProviderInterface;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Iban;
use Symfony\Component\Validator\Constraints\NotBlank;
use Tests\Madcoders\SyliusRmaPlugin\Unit\UnitTestCase;

class ReturnFormTypeTest extends UnitTestCase
{
    use ProphecyTrait;

    /** @test */
    function the_standard_return_form_does_not_add_the_additional_information_fields_itself()
    {
        // the gated section lives in ReturnFormTypeExtension, so the base type must stay neutral
        $builder = $this->prophesize(FormBuilderInterface::class);
        $double = $builder->reveal();
        $builder->add(Argument::cetera())->willReturn($double);
        $builder->addEventListener(Argument::cetera())->willReturn($double);

        $returnForm = new ReturnFormType($this->prophesize(ChoiceProviderInterface::class)->reveal());
        $returnForm->buildForm($double, []);

        $builder->add('bankAccountNumber', Argument::cetera())->shouldNotHaveBeenCalled();
        $builder->add('accountHolderName', Argument::cetera())->shouldNotHaveBeenCalled();
        $builder->add('bankName', Argument::cetera())->shouldNotHaveBeenCalled();
    }

    /** @test */
    function the_withdrawal_form_always_keeps_the_bank_account_field_regardless_of_the_flag()
    {
        // the type extension does not reach this subtype, so the withdrawal form adds the field itself
        $builder = $this->prophesize(FormBuilderInterface::class);
        $double = $builder->reveal();
        $builder->add(Argument::cetera())->willReturn($double);
        $builder->addEventListener(Argument::cetera())->willReturn($double);

        $withdrawalForm = new WithdrawalReturnFormType($this->prophesize(ChoiceProviderInterface::class)->reveal());
        $withdrawalForm->buildForm($double, []);

        // then the bank account field stays required, but the new fields are not added
        $builder->add('bankAccountNumber', Argument::any(), Argument::that($this->hasConstraints(NotBlank::class, Iban::class)))
            ->shouldHaveBeenCalled();
        $builder->add('accountHolderName', Argument::cetera())->shouldNotHaveBeenCalled();
        $builder->add('bankName', Argument::cetera())->shouldNotHaveBeenCalled();
    }

    /** @test */
    function the_customer_email_field_must_be_a_valid_email(): void
    {
        // the customerEmail value must be a syntactically valid address; the return document
        // recipient is re-derived from the order server-side (see security issue #28)
        $builder = $this->prophesize(FormBuilderInterface::class);
        $double = $builder->reveal();
        $builder->add(Argument::cetera())->willReturn($double);
        $builder->addEventListener(Argument::cetera())->willReturn($double);

        $returnForm = new ReturnFormType($this->prophesize(ChoiceProviderInterface::class)->reveal());
        $returnForm->buildForm($double, []);

        $builder->add('customerEmail', Argument::any(), Argument::that($this->hasConstraints(NotBlank::class, Email::class)))
            ->shouldHaveBeenCalled();
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
