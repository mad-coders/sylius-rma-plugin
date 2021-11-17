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
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnInterface;
use Sylius\Behat\NotificationType;
use Sylius\Behat\Service\NotificationCheckerInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Tests\Madcoders\SyliusRmaPlugin\Behat\Page\Admin\Rma\OrderReturn\IndexPageInterface;
use Tests\Madcoders\SyliusRmaPlugin\Behat\Page\Admin\Rma\OrderReturn\ShowPageInterface;

class ReturnNotesContext implements Context
{
    /** @var ShowPageInterface */
    private $orderReturnShowPage;

    /** @var NotificationCheckerInterface */
    private $notificationChecker;

    /**
     * ReturnNotesContext constructor
     *
     * @param ShowPageInterface $orderReturnShowPage
     * @param NotificationCheckerInterface $notificationChecker
     */
    public function __construct(
        ShowPageInterface $orderReturnShowPage,
        NotificationCheckerInterface $notificationChecker
    )
    {
        $this->orderReturnShowPage = $orderReturnShowPage;
        $this->notificationChecker = $notificationChecker;
    }

    /**
     * @When I fill notes field with text :text
     */
    public function iFillNotesField(string $text): void
    {
        $this->orderReturnShowPage->fillNoteField($text);
    }

    /**
     * @When I click add note button
     */
    public function iClickAddNoteButton(): void
    {
        $this->orderReturnShowPage->clickSendNoteButton();
    }

    /**
     * @Then I should be notified that note has been successfully added
     */
    public function iShouldBeNotifiedAboutItHasBeenSuccessfullyAdded(): void
    {
        $this->notificationChecker->checkNotification(
            'The note has been added',
            NotificationType::success()
        );
    }

    /**
     * @Then I should see time line comment with text :text
     * @throws \Exception
     */
    public function iShouldSeeComment(string $text): void
    {
        $note = $this->orderReturnShowPage->getFirstTimelineNotes();
        if (strcasecmp($text, $note->getText()) !== 0) {
            throw new \Exception(sprintf('Order return has "%s" status', $note->getText()));
        }
    }
}
