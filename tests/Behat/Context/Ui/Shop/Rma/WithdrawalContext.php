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
use Doctrine\Persistence\ObjectManager;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnChangeLog;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Behat\Service\Checker\EmailCheckerInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Tests\Madcoders\SyliusRmaPlugin\Behat\Page\Shop\Rma\WithdrawalPageInterface;
use Webmozart\Assert\Assert;

final class WithdrawalContext implements Context
{
    /**
     * @param RepositoryInterface<OrderReturnInterface> $orderReturnRepository
     * @param RepositoryInterface<OrderReturnChangeLog> $changeLogRepository
     */
    public function __construct(
        private readonly WithdrawalPageInterface $withdrawalPage,
        private readonly RepositoryInterface $orderReturnRepository,
        private readonly RepositoryInterface $changeLogRepository,
        private readonly EmailCheckerInterface $emailChecker,
        private readonly TranslatorInterface $translator,
        private readonly ObjectManager $orderManager,
    ) {
    }

    /**
     * @Then /^I should be on the order withdrawal page for (latest order)$/
     */
    public function iShouldBeOnTheOrderWithdrawalPage(OrderInterface $order): void
    {
        $this->withdrawalPage->verify(['orderNumber' => $this->plainNumber($order)]);
        Assert::true($this->withdrawalPage->hasConfirmButton());
    }

    /**
     * @When I confirm the withdrawal
     */
    public function iConfirmTheWithdrawal(): void
    {
        $this->withdrawalPage->confirm();
    }

    /**
     * @Then /^order return for (latest order) should have status "([^"]+)"$/
     */
    public function orderReturnForOrderShouldHaveStatus(OrderInterface $order, string $status): void
    {
        Assert::same($this->findReturnForOrder($order)->getOrderReturnStatus(), $status);
    }

    /**
     * @Then /^(latest order) should be cancelled$/
     */
    public function orderShouldBeCancelled(OrderInterface $order): void
    {
        // the order was cancelled in a separate request; refresh the stale managed entity
        $this->orderManager->refresh($order);
        Assert::same($order->getState(), OrderInterface::STATE_CANCELLED);
    }

    /**
     * @Then /^a withdrawal "([^"]+)" email should be sent to "([^"]+)" for (latest order)$/
     */
    public function aWithdrawalEmailShouldBeSentTo(string $type, string $recipient, OrderInterface $order, string $localeCode = 'en_US'): void
    {
        $orderReturn = $this->findReturnForOrder($order);

        $message = $this->translator->trans(
            sprintf('madcoders_rma.email.withdrawal_%s.info', $type),
            ['%orderNumber%' => $orderReturn->getOrderNumber()],
            null,
            $localeCode,
        );

        Assert::true($this->emailChecker->hasMessageTo($message, $recipient));
    }

    /**
     * @Then /^order return for (latest order) should have a "([^"]+)" change-log entry authored by a (customer|admin)$/
     */
    public function orderReturnForOrderShouldHaveChangeLogEntryAuthoredBy(OrderInterface $order, string $type, string $authorType): void
    {
        $returnNumber = $this->findReturnForOrder($order)->getReturnNumber();

        $changeLog = $this->changeLogRepository->findOneBy(['returnNumber' => $returnNumber, 'type' => $type]);
        Assert::isInstanceOf($changeLog, OrderReturnChangeLog::class);
        Assert::same($changeLog->getAuthor()->getType(), $authorType);
    }

    private function findReturnForOrder(OrderInterface $order): OrderReturnInterface
    {
        $orderReturn = $this->orderReturnRepository->findOneBy(['orderNumber' => $this->plainNumber($order)]);
        Assert::isInstanceOf($orderReturn, OrderReturnInterface::class);

        return $orderReturn;
    }

    private function plainNumber(OrderInterface $order): string
    {
        return str_replace('#', '', (string) $order->getNumber());
    }
}
