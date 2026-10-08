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

use Madcoders\SyliusRmaPlugin\Form\Type\GracePeriodType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\Validator\Validation;
use Tests\Madcoders\SyliusRmaPlugin\Unit\UnitTestCase;

class GracePeriodTypeTest extends UnitTestCase
{
    private FormFactoryInterface $formFactory;

    protected function setUp(): void
    {
        $this->formFactory = Forms::createFormFactoryBuilder()
            ->addExtension(new ValidatorExtension(Validation::createValidator()))
            ->getFormFactory()
        ;
    }

    /** @test */
    function it_accepts_a_whole_number_of_days_within_the_allowed_range()
    {
        $form = $this->submit(['reason' => 'damaged', 'extraDays' => '20', 'note' => 'Carrier delay']);

        $this->assertTrue($form->isValid());
        $this->assertSame(['reason' => 'damaged', 'extraDays' => '20', 'note' => 'Carrier delay'], $form->getData());
    }

    /** @test */
    function it_accepts_the_range_boundaries()
    {
        $this->assertTrue($this->submit(['reason' => 'damaged', 'extraDays' => '1'])->isValid());
        $this->assertTrue($this->submit(['reason' => 'damaged', 'extraDays' => '365'])->isValid());
    }

    /** @test */
    function it_rejects_zero_days()
    {
        $form = $this->submit(['reason' => 'damaged', 'extraDays' => '0']);

        $this->assertFalse($form->isValid());
        $this->assertSame(
            'madcoders_rma.validator.grace_period.extra_days.range',
            $form->get('extraDays')->getErrors()[0]?->getMessageTemplate(),
        );
    }

    /** @test */
    function it_rejects_more_than_a_year()
    {
        $this->assertFalse($this->submit(['reason' => 'damaged', 'extraDays' => '366'])->isValid());
    }

    /** @test */
    function it_rejects_a_value_that_is_not_a_whole_number()
    {
        $form = $this->submit(['reason' => 'damaged', 'extraDays' => '10.5']);

        $this->assertFalse($form->isValid());
        $this->assertSame(
            'madcoders_rma.validator.grace_period.extra_days.integer',
            $form->get('extraDays')->getErrors()[0]?->getMessageTemplate(),
        );
        $this->assertFalse($this->submit(['reason' => 'damaged', 'extraDays' => 'abc'])->isValid());
    }

    /** @test */
    function it_rejects_missing_days()
    {
        $this->assertFalse($this->submit(['reason' => 'damaged', 'extraDays' => ''])->isValid());
    }

    /** @test */
    function it_rejects_a_missing_or_unknown_reason()
    {
        $this->assertFalse($this->submit(['reason' => '', 'extraDays' => '20'])->isValid());
        $this->assertFalse($this->submit(['reason' => 'unknown', 'extraDays' => '20'])->isValid());
    }

    /**
     * @param array<string, string> $data
     */
    private function submit(array $data): \Symfony\Component\Form\FormInterface
    {
        $form = $this->formFactory->create(GracePeriodType::class, null, [
            'reasons' => ['Damaged' => 'damaged', 'Wrong size' => 'wrong_size'],
        ]);
        $form->submit($data);

        return $form;
    }
}
