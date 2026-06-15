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

use FriendsOfBehat\PageObjectExtension\Page\SymfonyPage;

class WithdrawalPage extends SymfonyPage implements WithdrawalPageInterface
{
    public function getRouteName(): string
    {
        return 'madcoders_rma_withdrawal';
    }

    public function hasConfirmButton(): bool
    {
        return $this->hasElement('confirm_button');
    }

    public function confirm(): void
    {
        $this->getElement('confirm_button')->press();
    }

    public function hasReturnForm(): bool
    {
        return $this->hasElement('rma_submit_return_form');
    }

    /**
     * @inheritdoc
     */
    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'confirm_button' => '[data-test-withdraw-confirm-button]',
            'rma_submit_return_form' => '[data-test-madcoders-rma-submit-return-form-button]',
        ]);
    }
}
