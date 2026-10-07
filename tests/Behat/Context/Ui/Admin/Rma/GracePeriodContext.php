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

namespace Tests\Madcoders\SyliusRmaPlugin\Behat\Context\Ui\Admin\Rma;

use Behat\Behat\Context\Context;
use Sylius\Behat\NotificationType;
use Sylius\Behat\Service\NotificationCheckerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Tests\Madcoders\SyliusRmaPlugin\Behat\Page\Admin\Rma\Order\ShowPageInterface;
use Webmozart\Assert\Assert;

class GracePeriodContext implements Context
{
    public function __construct(
        private ShowPageInterface $orderShowPage,
        private NotificationCheckerInterface $notificationChecker,
    ) {
    }

    /**
     * @When /^I open the admin page of (the order)$/
     */
    public function iOpenTheAdminPageOfTheOrder(OrderInterface $order): void
    {
        $this->orderShowPage->open(['id' => $order->getId()]);
    }

    /**
     * @When /^I grant (\d+) extra days for the return reason "([^"]+)"$/
     * @When /^I try to grant "([^"]*)" extra days for the return reason "([^"]+)"$/
     */
    public function iGrantExtraDays(string $days, string $reasonCode): void
    {
        $this->orderShowPage->grantGracePeriod($reasonCode, $days);
    }

    /**
     * @When /^I grant (\d+) extra days for the return reason "([^"]+)" with note "([^"]+)"$/
     */
    public function iGrantExtraDaysWithNote(string $days, string $reasonCode, string $note): void
    {
        $this->orderShowPage->grantGracePeriod($reasonCode, $days, $note);
    }

    /**
     * @When /^I revoke the grace period of the return reason "([^"]+)"$/
     */
    public function iRevokeTheGracePeriod(string $reasonCode): void
    {
        $this->orderShowPage->revokeGracePeriod($reasonCode);
    }

    /**
     * @Then /^I should be notified that the grace period has been (granted|changed|revoked)$/
     */
    public function iShouldBeNotifiedThatTheGracePeriodHasBeen(string $action): void
    {
        $messages = [
            'granted' => 'The grace period has been granted.',
            'changed' => 'The grace period has been changed.',
            'revoked' => 'The grace period has been revoked.',
        ];

        $this->notificationChecker->checkNotification($messages[$action], NotificationType::success());
    }

    /**
     * @Then I should be notified with the error :message
     */
    public function iShouldBeNotifiedWithTheError(string $message): void
    {
        $this->notificationChecker->checkNotification($message, NotificationType::failure());
    }

    /**
     * @Then /^the return reason "([^"]+)" should have (\d+) extra days on the order page$/
     */
    public function theReturnReasonShouldHaveExtraDays(string $reasonCode, string $days): void
    {
        Assert::same($this->orderShowPage->getExtraDays($reasonCode), $days);
    }

    /**
     * @Then /^the return reason "([^"]+)" should have no extra days on the order page$/
     */
    public function theReturnReasonShouldHaveNoExtraDays(string $reasonCode): void
    {
        Assert::same($this->orderShowPage->getExtraDays($reasonCode), 'None');
    }

    /**
     * @Then the latest grace period history entry should contain :text
     */
    public function theLatestHistoryEntryShouldContain(string $text): void
    {
        $entries = $this->orderShowPage->getHistoryEntries();
        Assert::notEmpty($entries, 'Expected at least one grace period history entry.');
        Assert::contains($entries[0], $text);
    }
}
