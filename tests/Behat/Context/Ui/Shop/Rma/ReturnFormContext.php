<?php

declare(strict_types=1);

namespace Tests\Madcoders\SyliusRmaPlugin\Behat\Context\Ui\Shop\Rma;

use Behat\Behat\Context\Context;
use Sylius\Component\Core\Model\OrderInterface;
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
     * @When /^I should be redirected to order return page for (latest order)$/
     */
    public function shouldBeOnOrderReturnPage(OrderInterface $order): void
    {
        $this->returnFormPage->verify(['orderNumber' => str_replace('#', '', $order->getNumber())]);
    }
}
