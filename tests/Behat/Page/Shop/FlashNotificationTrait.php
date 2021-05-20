<?php

declare(strict_types=1);

namespace Tests\Madcoders\SyliusRmaPlugin\Behat\Page\Shop;

use Behat\Mink\Element\DocumentElement;
use Behat\Mink\Element\NodeElement;

/**
 * @method getDocument(): DocumentElement
 */
trait FlashNotificationTrait
{
    /**
     * @return string[]
     */
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
