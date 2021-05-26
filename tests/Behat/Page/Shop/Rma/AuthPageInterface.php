<?php

declare(strict_types=1);

namespace Tests\Madcoders\SyliusRmaPlugin\Behat\Page\Shop\Rma;

use Behat\Mink\Element\NodeElement;
use FriendsOfBehat\PageObjectExtension\Page\SymfonyPageInterface;

interface AuthPageInterface extends SymfonyPageInterface
{
    public function getSubmitButton(): NodeElement;

    public function clickSubmitButton(): void;

    public function getAuthCodeFieled(): NodeElement;

    public function insertAuthCode(string $authCode): void;
}