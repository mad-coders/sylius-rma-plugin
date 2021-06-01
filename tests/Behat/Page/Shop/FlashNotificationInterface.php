<?php

declare(strict_types=1);

namespace Tests\Madcoders\SyliusRmaPlugin\Behat\Page\Shop;

interface FlashNotificationInterface
{
    /**
     * @return string[]
     */
    public function getNotifications(): array;
}
