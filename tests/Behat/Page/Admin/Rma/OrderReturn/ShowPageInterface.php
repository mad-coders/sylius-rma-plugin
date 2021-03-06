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

namespace Tests\Madcoders\SyliusRmaPlugin\Behat\Page\Admin\Rma\OrderReturn;

use Behat\Mink\Element\NodeElement;
use FriendsOfBehat\PageObjectExtension\Page\SymfonyPageInterface;

interface ShowPageInterface  extends SymfonyPageInterface
{
    public function isNewOrderReturnPage(): bool;

    public function completeThisOrderReturn(): void;

    public function cancelThisOrderReturn(): void;

    public function getRouteName(): string;

    public function getStatus(): string;

    public function fillNoteField(string $text): void;

    public function clickSendNoteButton(): void;

    public function getFirstTimelineNotes(): NodeElement;
}
