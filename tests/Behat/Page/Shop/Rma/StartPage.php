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
use FriendsOfBehat\PageObjectExtension\Page\SymfonyPage;

class StartPage extends SymfonyPage implements StartPageInterface
{
    public function getRouteName(): string
    {
        return 'madcoders_rma_start';
    }

    public function getOrderNumberField(): NodeElement
    {
        return $this->getElement('order_number_field');
    }

    public function hasOrderNumberField(): bool
    {
        return $this->hasElement('order_number_field');
    }

    public function getSubmitButton(): NodeElement
    {
        return $this->getElement('submit_button');
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'order_number_field' => '[data-test-rma-order-number-field]',
            'submit_button' => '[data-test-rma-submit-button]',
        ]);
    }
}
