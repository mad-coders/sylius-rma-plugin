<?php

declare(strict_types=1);

namespace Tests\Madcoders\SyliusRmaPlugin\Behat\Context\Ui\Admin\Rma;

use Behat\Behat\Context\Context;
use Tests\Madcoders\SyliusRmaPlugin\Behat\Page\Admin\Rma\AddressConfiguration\UpdatePageInterface;

class AddressConfigurationContext implements Context
{
    /** @var UpdatePageInterface */
    private $returnConsentUpdatePage;

    /**
     * AddressConfigurationContext constructor
     *
     * @param UpdatePageInterface $returnConsentUpdatePage
     */
    public function __construct(
        UpdatePageInterface $returnConsentUpdatePage
    )
    {
        $this->returnConsentUpdatePage = $returnConsentUpdatePage;
    }

    /**
     * @Given I am on configuration page
     */
    public function iAmOnConfigurationPage(): void
    {
        $this->returnConsentUpdatePage->open();
    }
}
