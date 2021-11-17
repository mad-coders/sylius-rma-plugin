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

namespace Tests\Madcoders\SyliusRmaPlugin\Behat\Context\Ui\Shop;

use FriendsOfBehat\PageObjectExtension\Page\SymfonyPageInterface;
use Tests\Madcoders\SyliusRmaPlugin\Behat\Page\Shop\FlashNotificationInterface;
use Webmozart\Assert\Assert;

trait FlashNotificationContextTrait
{
    protected abstract function getPage(): SymfonyPageInterface;

    /**
     * @Then I see single success message containing text :message
     */
    public function iSeeSuccessMessage(string $message): void
    {
        $flashMessages = $this->getNotifications();

        Assert::count($flashMessages, 1, 'Only one flash message is allowed');
        Assert::contains($flashMessages[0], $message, 'Flash message contains given string');
    }

    /**
     * It provides notifications for given page or page
     *
     * @param SymfonyPageInterface|null $page
     *
     * @return string[]
     */
    private function getNotifications(?SymfonyPageInterface $page = null): array
    {
        if ($page === null) {
            $page = $this->getPage();
        }

        if (!$page instanceof FlashNotificationInterface) {
            throw new \InvalidArgumentException(sprintf('Page "%s" does not support flash messages', get_class($page)));
        }

        return $page->getNotifications();
    }
}
