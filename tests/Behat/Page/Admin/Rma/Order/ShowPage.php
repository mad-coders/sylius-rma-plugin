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

use FriendsOfBehat\PageObjectExtension\Page\SymfonyPage;

class ShowPage extends SymfonyPage implements ShowPageInterface
{
    public function grantGracePeriod(string $reasonCode, string $extraDays, ?string $note = null): void
    {
        $document = $this->getDocument();
        $document->selectFieldOption('madcoders_rma_grace_period_reason', $reasonCode);
        $document->fillField('madcoders_rma_grace_period_extraDays', $extraDays);
        if (null !== $note) {
            $document->fillField('madcoders_rma_grace_period_note', $note);
        }

        $this->getElement('grace_period_save')->click();
    }

    public function revokeGracePeriod(string $reasonCode): void
    {
        $this->getElement('grace_period_revoke', ['%reasonCode%' => $reasonCode])->click();
    }

    public function getExtraDays(string $reasonCode): string
    {
        return trim($this->getElement('grace_period_extra_days', ['%reasonCode%' => $reasonCode])->getText());
    }

    public function getHistoryEntries(): array
    {
        $entries = [];
        foreach ($this->getElement('grace_period_history')->findAll('css', '[data-test-grace-period-history-entry]') as $entry) {
            $entries[] = trim((string) preg_replace('/\s+/', ' ', $entry->getText()));
        }

        return $entries;
    }

    public function getRouteName(): string
    {
        return 'sylius_admin_order_show';
    }

    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'grace_period_extra_days' => '[data-test-grace-period-extra-days="%reasonCode%"]',
            'grace_period_history' => '[data-test-grace-period-history]',
            'grace_period_revoke' => '[data-test-grace-period-revoke="%reasonCode%"]',
            'grace_period_save' => '[data-test-grace-period-save]',
        ]);
    }
}
