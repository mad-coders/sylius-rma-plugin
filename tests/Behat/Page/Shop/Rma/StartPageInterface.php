<?php

declare(strict_types=1);

namespace Tests\Madcoders\SyliusRmaPlugin\Behat\Page\Shop\Rma;

use Behat\Mink\Element\NodeElement;
use FriendsOfBehat\PageObjectExtension\Page\SymfonyPageInterface;

interface StartPageInterface extends SymfonyPageInterface
{
    public function getOrderNumberField(): NodeElement;

    public function hasOrderNumberField(): bool;

    public function getSubmitButton(): NodeElement;
}
