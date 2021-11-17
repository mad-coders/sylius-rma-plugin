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

use Behat\Mink\Exception\ElementNotFoundException;
use FriendsOfBehat\PageObjectExtension\Page\SymfonyPageInterface;

interface ReturnFormPageInterface extends SymfonyPageInterface
{
    /**
     * @throws ElementNotFoundException
     */
    public function selectsOption(string $locator, string $value): void;

    public function selectReturnReason(string $value): void;

    public function fillBankAccountField(): void;

    public function fillNoteField(string $noteText): void;

    public function submitThisOrderReturnForm(): void;
}
