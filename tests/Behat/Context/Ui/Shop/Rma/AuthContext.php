<?php

declare(strict_types=1);

namespace Tests\Madcoders\SyliusRmaPlugin\Behat\Context\Ui\Shop\Rma;

use Behat\Behat\Context\Context;
use Tests\Madcoders\SyliusRmaPlugin\Behat\Page\Shop\Rma\StartPageInterface;
use Webmozart\Assert\Assert;

class AuthContext implements Context
{
    /** @var StartPageInterface */
    private $startPage;

    public function __construct(StartPageInterface $startPage)
    {
        $this->startPage = $startPage;
    }

    /**
     * @Then I can see order number input field
     */
    public function iCanSeeOrderNumberField(): void
    {
        Assert::true($this->startPage->hasOrderNumberField());
    }

    /**
     * @When I visit RMA start page
     */
    public function visitStartPage(): void
    {
        $this->startPage->open();
    }

}
