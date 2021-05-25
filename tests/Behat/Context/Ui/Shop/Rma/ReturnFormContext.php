<?php

declare(strict_types=1);

namespace Tests\Madcoders\SyliusRmaPlugin\Behat\Context\Ui\Shop\Rma;

use Behat\Behat\Context\Context;
use Tests\Madcoders\SyliusRmaPlugin\Behat\Page\Shop\Rma\ReturnFormPageInterface;

class ReturnFormContext implements Context
{
    /** @var ReturnFormPageInterface */
    private $returnFormPage;

    public function __construct(ReturnFormPageInterface $returnFormPage)
    {
        $this->returnFormPage = $returnFormPage;
    }

    /**
     * @When I should be redirected to order return page for order :orderNumber
     */
    public function shouldBeOnOrderReturnPage(string $orderNumber): void
    {
        $this->returnFormPage->verify(['orderNumber' => $orderNumber]);
    }
}
