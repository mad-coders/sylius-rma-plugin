<?php

declare(strict_types=1);

namespace Tests\Madcoders\SyliusRmaPlugin\Behat\Page\Shop\Rma;

use FriendsOfBehat\PageObjectExtension\Page\SymfonyPage;

class OrderShowPage extends SymfonyPage implements OrderShowPageInterface
{
    public function clickReturnButton(): void
    {
        $this->getElement('return_order_button')->click();
    }

    public function getRouteName(): string
    {
        return 'sylius_shop_account_order_show';
    }

    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'return_order_button' => '[data-test-button="madcoders_rma.ui.action.create_new_return"]',
        ]);
    }
}
