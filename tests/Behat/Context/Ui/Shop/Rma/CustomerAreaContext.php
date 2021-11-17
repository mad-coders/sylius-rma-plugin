<?php

declare(strict_types=1);

namespace Tests\Madcoders\SyliusRmaPlugin\Behat\Context\Ui\Shop\Rma;

use Behat\Behat\Context\Context;
use Sylius\Component\Core\Model\OrderInterface;
use Tests\Madcoders\SyliusRmaPlugin\Behat\Page\Shop\Rma\DashboardPageInterface;
use Tests\Madcoders\SyliusRmaPlugin\Behat\Page\Shop\Rma\OrderIndexPageInterface;
use Tests\Madcoders\SyliusRmaPlugin\Behat\Page\Shop\Rma\OrderShowPageInterface;

class CustomerAreaContext implements Context
{
    /** @var DashboardPageInterface */
    private $dashboardPage;

    /** @var OrderIndexPageInterface */
    private $orderIndexPage;

    /** @var OrderShowPageInterface */
    private $orderShowPage;

    public function __construct(
        DashboardPageInterface $dashboardPage,
        OrderIndexPageInterface $orderIndexPage,
        OrderShowPageInterface $orderShowPage
    )
    {
        $this->dashboardPage = $dashboardPage;
        $this->orderIndexPage = $orderIndexPage;
        $this->orderShowPage = $orderShowPage;
    }

    /**
     * @Then I am on dashboard in customer area
     */
    public function iAmOnMyAccountDashboard(): void
    {
        $this->dashboardPage->isOpen();
    }

    /**
     * @When /^I click return button at (latest order)$/
     */
    public function iClickReturnButtonForLatestOrder(OrderInterface $order): void
    {
        $this->orderIndexPage->clickReturnButtonForLatestOrder($order);
    }

    /**
     * @When I click return button
     */
    public function iClickReturnButton(): void
    {
        $this->orderShowPage->clickReturnButton();
    }

    /**
     * @Given /^I am on orders show page for (latest order) in customer area$/
     */
    public function iAmOnOrderShowPage(OrderInterface $order): void
    {
        $this->orderShowPage->open(['number' => $order->getNumber()]);
    }
}
