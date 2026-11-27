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

use Madcoders\SyliusRmaPlugin\Entity\OrderReturnReasonTranslation;
use Madcoders\SyliusRmaPlugin\Form\Type\ReturnReasonTranslationType;
use Sylius\Component\Resource\Translation\Provider\TranslationLocaleProviderInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ContainerConstraintValidatorFactory;
use Symfony\Component\Validator\Validation;
use Tests\Madcoders\SyliusRmaPlugin\Unit\UnitTestCase;

class ReturnReasonTranslationTypeTest extends UnitTestCase
{
    private const DEFAULT_LOCALE = 'en_US';

    /** @test */
    function a_translation_filled_in_for_another_locale_requires_a_slug()
    {
        // it used to be saved with an empty slug, and the next one in that locale hit the
        // (locale, slug) unique index as an HTTP 500 (#66)
        $form = $this->submitTranslation('pl_PL', ['name' => 'Powód', 'slug' => '']);

        self::assertSame(['madcoders_rma.validator.slug.not_blank'], $this->errorMessages($form->get('slug')));
    }

    /** @test */
    function an_untouched_translation_for_another_locale_stays_optional()
    {
        $form = $this->submitTranslation('pl_PL', ['name' => '', 'slug' => '']);

        self::assertNull($form->getData(), 'An untouched optional locale should not produce a translation to save.');
        self::assertTrue($form->isValid());
    }

    /** @test */
    function the_default_locale_translation_requires_a_slug()
    {
        $form = $this->submitTranslation(self::DEFAULT_LOCALE, ['name' => 'Reason', 'slug' => '']);

        self::assertSame(['madcoders_rma.validator.slug.not_blank'], $this->errorMessages($form->get('slug')));
    }

    /** @test */
    function a_filled_in_slug_is_accepted()
    {
        $form = $this->submitTranslation('pl_PL', ['name' => 'Powód', 'slug' => 'powod']);

        self::assertTrue($form->isValid());
    }

    /** @test */
    function a_slug_already_used_in_the_locale_is_reported_on_the_slug_field()
    {
        $uniqueValidator = $this->uniqueEntityValidator(takenSlug: 'powod');

        $form = $this->submitTranslation('pl_PL', ['name' => 'Powód', 'slug' => 'powod'], $uniqueValidator);

        self::assertSame(['madcoders_rma.validator.slug.unique'], $this->errorMessages($form->get('slug')));
        self::assertSame(['locale', 'slug'], (array) $uniqueValidator->constraint?->fields);
    }

    /** @test */
    function the_uniqueness_is_not_checked_while_the_slug_is_empty()
    {
        // a legacy empty slug in the same locale would otherwise add "already used" on top of "please enter"
        $uniqueValidator = $this->uniqueEntityValidator(takenSlug: '');

        $form = $this->submitTranslation('pl_PL', ['name' => 'Powód', 'slug' => ''], $uniqueValidator);

        self::assertSame(['madcoders_rma.validator.slug.not_blank'], $this->errorMessages($form->get('slug')));
        self::assertNull($uniqueValidator->constraint);
    }

    /**
     * Submits the translation entry the way ResourceTranslationsType builds it: named after the
     * locale and required only for the default locale.
     *
     * @param array<string, string> $data
     */
    private function submitTranslation(string $localeCode, array $data, ?ConstraintValidator $uniqueValidator = null): FormInterface
    {
        $localeProvider = $this->createMock(TranslationLocaleProviderInterface::class);
        $localeProvider->method('getDefaultLocaleCode')->willReturn(self::DEFAULT_LOCALE);

        $uniqueValidator ??= $this->uniqueEntityValidator(takenSlug: null);
        $validator = Validation::createValidatorBuilder()
            ->setConstraintValidatorFactory(new ContainerConstraintValidatorFactory(new ServiceLocator([
                'doctrine.orm.validator.unique' => static fn () => $uniqueValidator,
            ])))
            ->getValidator()
        ;

        $formFactory = Forms::createFormFactoryBuilder()
            ->addExtension(new ValidatorExtension($validator))
            ->addType(new ReturnReasonTranslationType(OrderReturnReasonTranslation::class, $localeProvider))
            ->getFormFactory()
        ;

        $form = $formFactory->createNamed($localeCode, ReturnReasonTranslationType::class, null, [
            'required' => self::DEFAULT_LOCALE === $localeCode,
        ]);
        $form->submit($data);

        return $form;
    }

    /**
     * Stands in for Doctrine's UniqueEntityValidator: reports the given slug as already taken and
     * remembers the constraint it was called with.
     */
    private function uniqueEntityValidator(?string $takenSlug): ConstraintValidator
    {
        return new class($takenSlug) extends ConstraintValidator {
            public ?UniqueEntity $constraint = null;

            public function __construct(private readonly ?string $takenSlug)
            {
            }

            public function validate(mixed $value, Constraint $constraint): void
            {
                \assert($constraint instanceof UniqueEntity);
                $this->constraint = $constraint;

                if ($value instanceof OrderReturnReasonTranslation && $value->getSlug() === $this->takenSlug) {
                    $this->context->buildViolation((string) $constraint->message)
                        ->atPath((string) $constraint->errorPath)
                        ->addViolation()
                    ;
                }
            }
        };
    }

    /** @return string[] */
    private function errorMessages(FormInterface $form): array
    {
        $messages = [];
        foreach ($form->getErrors() as $error) {
            $messages[] = $error->getMessage();
        }

        return $messages;
    }
}
