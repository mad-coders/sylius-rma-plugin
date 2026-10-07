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

namespace Tests\Madcoders\SyliusRmaPlugin\Behat\Page\Admin\Rma\Order;

use FriendsOfBehat\PageObjectExtension\Page\SymfonyPageInterface;

/**
 * The Sylius admin order page, as far as the grace period panel (#67) is concerned.
 */
interface ShowPageInterface extends SymfonyPageInterface
{
    public function grantGracePeriod(string $reasonCode, string $extraDays, ?string $note = null): void;

    public function revokeGracePeriod(string $reasonCode): void;

    public function getExtraDays(string $reasonCode): string;

    /**
     * @return list<string> history entry texts, newest first
     */
    public function getHistoryEntries(): array;
}
