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

use Madcoders\SyliusRmaPlugin\Entity\OrderReturnConsentInterface;
use Madcoders\SyliusRmaPlugin\Form\Type\ConsentFormType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

class ConsentFormTypeTest extends TypeTestCase
{
    protected function getExtensions(): array
    {
        return [new ValidatorExtension(Validation::createValidator())];
    }

    /** @test */
    function an_inline_consent_renders_its_description_as_the_html_checkbox_label()
    {
        $form = $this->factory->create(ConsentFormType::class, [
            'code' => 'consent_inline',
            'label' => 'Consent name',
            'consentRequire' => false,
            'fieldType' => OrderReturnConsentInterface::FIELD_TYPE_INLINE,
            'inlineHtml' => 'I accept the <a href="/terms">terms</a>',
        ]);

        $checkbox = $form->get('checked')->getConfig();

        self::assertSame('I accept the <a href="/terms">terms</a>', $checkbox->getOption('label'));
        self::assertTrue($checkbox->getOption('label_html'));
    }

    /** @test */
    function an_external_page_consent_keeps_the_plain_name_label()
    {
        $form = $this->factory->create(ConsentFormType::class, [
            'code' => 'consent_ext',
            'label' => 'Consent name',
            'consentRequire' => false,
            'fieldType' => OrderReturnConsentInterface::FIELD_TYPE_EXTERNAL_PAGE,
            'inlineHtml' => '',
        ]);

        $checkbox = $form->get('checked')->getConfig();

        self::assertSame('Consent name', $checkbox->getOption('label'));
        self::assertNotTrue($checkbox->getOption('label_html'));
    }

    /** @test */
    function an_inline_consent_without_a_description_falls_back_to_the_name_label()
    {
        $form = $this->factory->create(ConsentFormType::class, [
            'code' => 'consent_inline',
            'label' => 'Consent name',
            'consentRequire' => false,
            'fieldType' => OrderReturnConsentInterface::FIELD_TYPE_INLINE,
            'inlineHtml' => '',
        ]);

        $checkbox = $form->get('checked')->getConfig();

        self::assertSame('Consent name', $checkbox->getOption('label'));
        self::assertNotTrue($checkbox->getOption('label_html'));
    }

    /** @test */
    function a_required_consent_carries_the_is_true_constraint()
    {
        $form = $this->factory->create(ConsentFormType::class, [
            'code' => 'consent_ext',
            'label' => 'Consent name',
            'consentRequire' => true,
            'fieldType' => OrderReturnConsentInterface::FIELD_TYPE_EXTERNAL_PAGE,
            'inlineHtml' => '',
        ]);

        $constraints = $form->get('checked')->getConfig()->getOption('constraints');

        self::assertCount(1, $constraints);
        self::assertInstanceOf(\Symfony\Component\Validator\Constraints\IsTrue::class, $constraints[0]);
    }
}
