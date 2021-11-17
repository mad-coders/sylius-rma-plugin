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
