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
use Behat\Mink\Exception\ElementNotFoundException;
use FriendsOfBehat\PageObjectExtension\Page\SymfonyPage;

class ShowPage extends SymfonyPage implements ShowPageInterface
{
    public function isNewOrderReturnPage(): bool
    {
        return $this->hasElement('rma-complete-button');
    }

    public function completeThisOrderReturn(): void
    {
        try {
            $this->getElement('rma-complete-button')->click();
        } catch (ElementNotFoundException $e) {
        }
    }

    public function cancelThisOrderReturn(): void
    {
        try {
            $this->getElement('rma-cancel-button')->click();
        } catch (ElementNotFoundException $e) {
        }
    }

    public function getStatus(): string
    {
       return $this->getElement('sylius-order-state')->getText();
    }

    public function fillNoteField(string $text): void
    {
        try {
            $this->getElement('rma_return_notes_note')->setValue($text);
         } catch (ElementNotFoundException $e) {
        }
    }

    public function getFirstTimelineNotes(): NodeElement
    {
        return $this->getElement('rma-timeline')->find('css', '[data-test-madcoders-rma-timeline-note-text]');
    }

    public function clickSendNoteButton(): void
    {
        try {
            $this->getElement('rma_send_note_button')->click();
        } catch (ElementNotFoundException $e) {
        }
    }

    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'rma-complete-button' =>  '.complete-button',
            'rma-cancel-button' =>  '.cancel-button',
            'sylius-order-state' => '#sylius-order-state',
            'rma-add-note-to-timeline' => '[data-test-madcoders-rma-add-note-to-timeline]',
            'rma_return_notes_note' => '#madcoders_rma_return_notes_note',
            'rma_send_note_button' => '[data-test-send-note-button]',
            'rma-timeline' => '[data-test-madcoders-rma-timeline]',
            'rma-timeline-note-text' => '[data-test-madcoders-rma-timeline-note-text]'
        ]);
    }

    public function getRouteName(): string
    {
        return 'madcoders_rma_admin_order_return_show';
    }
}
