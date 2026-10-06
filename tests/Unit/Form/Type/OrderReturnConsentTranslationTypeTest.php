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

use Madcoders\SyliusRmaPlugin\Entity\OrderReturnConsentTranslation;
use Madcoders\SyliusRmaPlugin\Form\Type\OrderReturnConsentTranslationType;
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

class OrderReturnConsentTranslationTypeTest extends UnitTestCase
{
    /** @test */
    function a_slug_already_used_in_the_locale_is_reported_on_the_slug_field()
    {
        $uniqueValidator = $this->uniqueEntityValidator(takenSlug: 'terms');

        $form = $this->submitTranslation(['name' => 'Terms', 'slug' => 'terms'], $uniqueValidator);

        self::assertSame(['madcoders_rma.validator.slug.unique'], $this->errorMessages($form->get('slug')));
        self::assertSame(['locale', 'slug'], (array) $uniqueValidator->constraint?->fields);
    }

    /** @test */
    function a_consent_without_a_slug_is_saved_with_a_null_slug_the_uniqueness_check_ignores()
    {
        // several inline consents per locale leave the slug empty; storing '' made the second one
        // collide on the (locale, slug) unique index (#69)
        $uniqueValidator = $this->uniqueEntityValidator(takenSlug: null);

        $form = $this->submitTranslation(['name' => 'Inline', 'slug' => '', 'description' => 'I accept'], $uniqueValidator);

        self::assertTrue($form->isValid());
        self::assertInstanceOf(OrderReturnConsentTranslation::class, $form->getData());
        self::assertNull($form->getData()->getSlug());
        self::assertTrue($uniqueValidator->constraint?->ignoreNull);
    }

    /** @param array<string, string> $data */
    private function submitTranslation(array $data, ConstraintValidator $uniqueValidator): FormInterface
    {
        $validator = Validation::createValidatorBuilder()
            ->setConstraintValidatorFactory(new ContainerConstraintValidatorFactory(new ServiceLocator([
                'doctrine.orm.validator.unique' => static fn () => $uniqueValidator,
            ])))
            ->getValidator()
        ;

        $formFactory = Forms::createFormFactoryBuilder()
            ->addExtension(new ValidatorExtension($validator))
            ->addType(new OrderReturnConsentTranslationType(OrderReturnConsentTranslation::class))
            ->getFormFactory()
        ;

        $form = $formFactory->createNamed('en_US', OrderReturnConsentTranslationType::class);
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

                if ($value instanceof OrderReturnConsentTranslation && null !== $this->takenSlug && $value->getSlug() === $this->takenSlug) {
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
