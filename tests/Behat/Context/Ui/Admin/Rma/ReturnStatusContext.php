<?php

declare(strict_types=1);

namespace Tests\Madcoders\SyliusRmaPlugin\Behat\Context\Ui\Admin\Rma;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnInterface;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnReasonInterface;
use Sylius\Behat\Service\SharedStorageInterface;
use Sylius\Component\Core\Model\AdminUserInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Tests\Madcoders\SyliusRmaPlugin\Behat\Page\Admin\Rma\OrderReturn\IndexPageInterface;
use Tests\Madcoders\SyliusRmaPlugin\Behat\Page\Admin\Rma\OrderReturn\ShowPageInterface;
use Webmozart\Assert\Assert;

class ReturnStatusContext implements Context
{
    /** @var IndexPageInterface */
    private $orderReturnIndexPage;

    /** @var ShowPageInterface */
    private $orderReturnShowPage;

    /** @var RepositoryInterface */
    private $orderReturnRepository;

    /**
     * ReturnContext constructor
     *
     * @param IndexPageInterface $orderReturnIndexPage
     * @param ShowPageInterface $orderReturnShowPage
     * @param RepositoryInterface $orderReturnRepository
     */
    public function __construct(
        IndexPageInterface $orderReturnIndexPage,
        ShowPageInterface $orderReturnShowPage,
        RepositoryInterface $orderReturnRepository
    )
    {
        $this->orderReturnIndexPage = $orderReturnIndexPage;
        $this->orderReturnShowPage = $orderReturnShowPage;
        $this->orderReturnRepository = $orderReturnRepository;
    }

    /**
     * @Given I am on order return index page
     */
    public function iAmOnOrderReturnIndexPage(): void
    {
        $this->openOrderReturnIndexPage();

        $this->orderReturnIndexPage->verify();
    }

    /**
     * @Given order return with number :orderReturnNumber has reason with code :reasonCode
     */
    public function orderReturnHasReason(string $orderReturnNumber, string $reasonCode): void
    {
        $orderReturn = $this->findOrderReturnByNumber($orderReturnNumber);
        $orderReturn->setReturnReason($reasonCode);

        $this->orderReturnRepository->add($orderReturn);
    }

    /**
     * @When I open order return :orderReturnNumber page
     * @Given I am on order return show page of return number :orderReturnNumber
     */
    public function iOpenOrderReturnPage(string $orderReturnNumber): void
    {
       $orderReturn = $this->findOrderReturnByNumber($orderReturnNumber);
       $this->orderReturnShowPage->open(['id' => $orderReturn->getId()]);
    }

    /**
     * @Then I should be redirected to order return show page of return number :orderReturnNumber
     */
    public function iAmOnOrderReturnPage(string $orderReturnNumber): void
    {
        $orderReturn = $this->findOrderReturnByNumber($orderReturnNumber);
        $this->orderReturnShowPage->verify(['id' => $orderReturn->getId()]);
    }

    /**
     * @When I click complete button
     */
    public function iCompleteThisButton(): void
    {
        if ($this->orderReturnShowPage->isNewOrderReturnPage()) {
            $this->orderReturnShowPage->completeThisOrderReturn();
        }
    }

    private function findOrderReturnByNumber(string $number): OrderReturnInterface
    {
        if (!$orderReturn = $this->orderReturnRepository->findOneBy(['returnNumber' => $number])) {
            throw new \InvalidArgumentException('$orderReturn cannot be found');
        }

        if (!$orderReturn instanceof OrderReturnInterface) {
            throw new \InvalidArgumentException(sprintf('Order Return "%s" does not support', get_class($orderReturn)));
        }

        return $orderReturn;
    }

    private function openOrderReturnIndexPage(): void
    {
        $this->orderReturnIndexPage->open();
    }
}
