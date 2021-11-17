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

namespace Tests\Madcoders\SyliusRmaPlugin\Behat\Page\Shop;

use Behat\Mink\Element\NodeElement;

/** @method getDocument(): DocumentElement */
trait FlashNotificationTrait
{
    /** @return string[] */
    public function getNotifications(): array
    {
        /** @var NodeElement[] $notificationElements */
        $notificationElements = $this->getDocument()->findAll('css', '[data-test-flash-messages]');
        $notifications = [];

        foreach ($notificationElements as $notificationElement) {
            $notifications[] = $notificationElement->getText();
        }

        return $notifications;
    }
}
