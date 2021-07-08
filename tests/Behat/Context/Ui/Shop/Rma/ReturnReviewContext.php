<?php

declare(strict_types=1);

namespace Tests\Madcoders\SyliusRmaPlugin\Behat\Context\Ui\Shop\Rma;

use Behat\Behat\Context\Context;
use FriendsOfBehat\PageObjectExtension\Page\SymfonyPageInterface;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Tests\Madcoders\SyliusRmaPlugin\Behat\Context\Ui\Shop\FlashNotificationContextTrait;
use Tests\Madcoders\SyliusRmaPlugin\Behat\Page\Shop\Rma\ReturnReviewPageInterface;

/**
 * Sylius RMA Plugin
 *
 * @copyright MADCODERS Team (www.madcoders.co)
 * @licence For the full copyright and license information, please view the LICENSE
 *
 * Architects of this package:
 * @author Leonid Moshko <l.moshko@madcoders.pl>
 * @author Piotr Lewandowski <p.lewandowski@madcoders.pl>
 */
class ReturnReviewContext implements Context
{
    use FlashNotificationContextTrait;

    /** @var ReturnReviewPageInterface */
    private $returnReviewPage;

    /** @var RepositoryInterface */
    private $orderReturnRepository;

    /**
     * ReturnReviewContext constructor
     *
     * @param ReturnReviewPageInterface $returnReviewPage
     * @param RepositoryInterface $orderReturnRepository
     */
    public function __construct(
        ReturnReviewPageInterface $returnReviewPage,
        RepositoryInterface $orderReturnRepository
    )
    {
        $this->returnReviewPage = $returnReviewPage;
        $this->orderReturnRepository = $orderReturnRepository;
    }

    /**
     * @Given /^I am on order return review page for (latest order)$/
     */
    public function iamOnReturnReviewPage(OrderInterface $order): void
    {
        $returnNumber = $this->findReturnFormDraftByOrderNumber($order->getNumber());
        $this->returnReviewPage->open(['returnNumber' => $returnNumber]);
    }

    /**
     * @When I approve return form
     */
    public function iApproveReturnForm(): void
    {
        $this->returnReviewPage->approveThisOrderReturnForm();
    }

    /**
     * @Then /^I should be redirected to return review page for (latest order)$/
     *
     * @throws \Exception
     */
    public function iShouldBeRedirectedReturnReviewPage(OrderInterface $order): void
    {
        $returnNumber = $this->findReturnFormDraftByOrderNumber($order->getNumber());
        $this->returnReviewPage->verify(['returnNumber' => $returnNumber]);
    }

    /**
     * @throws \Exception
     */
    private function findReturnFormDraftByOrderNumber(string $orderNumber): string
    {
        $orderReturnDraftList = $this->orderReturnRepository->findBy([
            'orderNumber' => str_replace('#', '', $orderNumber),
            'orderReturnStatus' => 'draft'
        ]);

        if (count($orderReturnDraftList) < 1 ) {
            $orderReturnDraftList = $this->orderReturnRepository->findBy([
                'orderNumber' => $orderNumber,
                'orderReturnStatus' => 'draft'
            ]);
        }

        if (!($orderReturnDraft = $orderReturnDraftList[0]) instanceof OrderReturnInterface) {
            throw new \Exception(sprintf('Could not find a draft for order "%s"', $orderNumber));
        }

        return $orderReturnDraft->getReturnNumber();
    }

    /**
     * @return SymfonyPageInterface
     */
    private function getPage(): SymfonyPageInterface
    {
        return $this->returnReviewPage;
    }
}
