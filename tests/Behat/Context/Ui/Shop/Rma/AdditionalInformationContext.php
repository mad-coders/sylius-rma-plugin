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

namespace Tests\Madcoders\SyliusRmaPlugin\Behat\Context\Ui\Shop\Rma;

use Behat\Behat\Context\Context;
use Tests\Madcoders\SyliusRmaPlugin\Behat\Page\Shop\Rma\ReturnFormPageInterface;
use Tests\Madcoders\SyliusRmaPlugin\Behat\Service\TogglableAdditionalInformationChecker;
use Webmozart\Assert\Assert;

final class AdditionalInformationContext implements Context
{
    public function __construct(
        private readonly TogglableAdditionalInformationChecker $additionalInformationChecker,
        private readonly ReturnFormPageInterface $returnFormPage,
    ) {
    }

    /**
     * @BeforeScenario
     * @AfterScenario
     */
    public function resetTheFlag(): void
    {
        $this->additionalInformationChecker->disable();
    }

    /**
     * @Given the return form requires additional information
     */
    public function theReturnFormRequiresAdditionalInformation(): void
    {
        $this->additionalInformationChecker->enable();
    }

    /**
     * @When I fill in my bank account with :iban
     */
    public function iFillInMyBankAccountWith(string $iban): void
    {
        $this->returnFormPage->fillBankAccountFieldWith($iban);
    }

    /**
     * @When I fill in the account holder name as :name
     */
    public function iFillInTheAccountHolderName(string $name): void
    {
        $this->returnFormPage->fillAccountHolderName($name);
    }

    /**
     * @When I fill in the bank name as :name
     */
    public function iFillInTheBankName(string $name): void
    {
        $this->returnFormPage->fillBankName($name);
    }

    /**
     * @Then the return form should show the additional information section
     */
    public function theReturnFormShouldShowTheAdditionalInformationSection(): void
    {
        Assert::true(
            $this->returnFormPage->hasAdditionalInformationSection(),
            'Expected the additional information section to be rendered, but it was not.',
        );
    }

    /**
     * @Then the return form should not show the additional information section
     */
    public function theReturnFormShouldNotShowTheAdditionalInformationSection(): void
    {
        Assert::false(
            $this->returnFormPage->hasAdditionalInformationSection(),
            'Expected the additional information section to be hidden, but it was rendered.',
        );
    }

    /**
     * @Then I should see the validation message :message
     */
    public function iShouldSeeTheValidationMessage(string $message): void
    {
        Assert::true(
            $this->returnFormPage->hasValidationMessage($message),
            sprintf('Expected to see the validation message "%s", but it was not found on the page.', $message),
        );
    }
}
