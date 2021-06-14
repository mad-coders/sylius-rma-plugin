<?php

declare(strict_types=1);

namespace Tests\Madcoders\SyliusRmaPlugin\Behat\Page\Admin\Rma\OrderReturn;


use FriendsOfBehat\PageObjectExtension\Page\SymfonyPage;

/**
 * Sylius RMA Plugin
 *
 * @copyright MADCODERS Team (www.madcoders.co)
 * @licence For the full copyright and license information, please view the LICENSE
 *
 * Architects of this package:
 * @author Leonid Moshko <l.moshko@madcoders.pl>
 * @author Piotr Lewandowski <p.lewandowski@madcoders.pl>
 */
class ShowPage extends SymfonyPage implements ShowPageInterface
{
    public function isNewOrderReturnPage(): bool
    {
        return $this->hasElement('rma-complete-button');
    }

    public function completeThisOrderReturn(): void
    {
        $this->getElement('rma-complete-button')->click();
    }

    public function cancelThisOrderReturn(): void
    {
        $this->getElement('rma-cancel-button')->click();
    }

    public function getStatus(): string
    {
       return $this->getElement('sylius-order-state')->getText();
    }

    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'rma-complete-button' =>  '.complete-button',
            'rma-cancel-button' =>  '.cancel-button',
            'sylius-order-state' => '#sylius-order-state'
        ]);
    }

    public function getRouteName(): string
    {
        return 'madcoders_rma_admin_order_return_show';
    }
}
