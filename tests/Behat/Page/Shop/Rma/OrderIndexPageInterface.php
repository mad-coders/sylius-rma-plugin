<?php

declare(strict_types=1);

namespace Tests\Madcoders\SyliusRmaPlugin\Behat\Page\Shop\Rma;

use FriendsOfBehat\PageObjectExtension\Page\SymfonyPageInterface;
use Sylius\Component\Core\Model\OrderInterface;

interface OrderIndexPageInterface extends SymfonyPageInterface
{
    public function clickReturnButtonForLatestOrder(OrderInterface $order);
}
