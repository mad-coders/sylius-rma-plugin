<?php

declare(strict_types=1);

namespace Tests\Madcoders\SyliusRmaPlugin\Behat\Page\Shop\Rma;

use FriendsOfBehat\PageObjectExtension\Page\SymfonyPageInterface;

interface OrderShowPageInterface extends SymfonyPageInterface
{
    public function clickReturnButton(): void;
}
