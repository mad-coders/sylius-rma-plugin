<?php

declare(strict_types=1);

namespace Tests\Madcoders\SyliusRmaPlugin\Behat\Context\Ui\Admin\Rma;

use Behat\Behat\Context\Context;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnInterface;
use Sylius\Behat\NotificationType;
use Sylius\Behat\Service\NotificationCheckerInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Tests\Madcoders\SyliusRmaPlugin\Behat\Page\Admin\Rma\OrderReturn\IndexPageInterface;
use Tests\Madcoders\SyliusRmaPlugin\Behat\Page\Admin\Rma\OrderReturn\ShowPageInterface;

class ReturnStatusContext implements Context
{
    /** @var IndexPageInterface */
    private $orderReturnIndexPage;

    /** @var ShowPageInterface */
    private $orderReturnShowPage;

    /** @var RepositoryInterface */
    private $orderReturnRepository;

    /** @var NotificationCheckerInterface */
    private $notificationChecker;

    /**
     * ReturnContext constructor
     *
     * @param IndexPageInterface $orderReturnIndexPage
     * @param ShowPageInterface $orderReturnShowPage
     * @param RepositoryInterface $orderReturnRepository
     * @param NotificationCheckerInterface $notificationChecker
     */
    public function __construct(
        IndexPageInterface $orderReturnIndexPage,
        ShowPageInterface $orderReturnShowPage,
        RepositoryInterface $orderReturnRepository,
        NotificationCheckerInterface $notificationChecker
    )
    {
        $this->orderReturnIndexPage = $orderReturnIndexPage;
        $this->orderReturnShowPage = $orderReturnShowPage;
        $this->orderReturnRepository = $orderReturnRepository;
        $this->notificationChecker = $notificationChecker;
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
     * @When order return show page of return number :orderReturnNumber will be refreshed
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
     * @Then I should be notified that status has been successfully updated
     */
    public function iShouldBeNotifiedAboutItHasBeenSuccessfullyCanceled()
    {
        $this->notificationChecker->checkNotification(
            'Order return has been successfully updated.',
            NotificationType::success()
        );
    }

    /**
     * @Then order return status is :status
     */
    public function orderReturnHasStatus(string $status): void
    {
       if (!$originalStatus = $this->orderReturnShowPage->getStatus()) {
           throw new \InvalidArgumentException('Order return status cannot be found');
       }
       if (strcasecmp($originalStatus, $status) !== 0) {
           throw new \Exception(sprintf('Order return has "%s" status', $originalStatus));
       }
    }

    /**
     * @When I click complete button
     */
    public function iCompleteThisOrderReturn(): void
    {
        if ($this->orderReturnShowPage->isNewOrderReturnPage()) {
            $this->orderReturnShowPage->completeThisOrderReturn();
        }
    }

    /**
     * @When I click cancel button
     */
    public function iCanceledThisOrderReturn(): void
    {
        $this->orderReturnShowPage->cancelThisOrderReturn();
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
