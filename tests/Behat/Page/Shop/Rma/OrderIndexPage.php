<?php

declare(strict_types=1);

namespace Tests\Madcoders\SyliusRmaPlugin\Behat\Page\Shop\Rma;

use Behat\Mink\Session;
use FriendsOfBehat\PageObjectExtension\Page\SymfonyPage;
use Sylius\Behat\Service\Accessor\TableAccessorInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Symfony\Component\Routing\RouterInterface;

class OrderIndexPage extends SymfonyPage implements OrderIndexPageInterface
{
    /** @var TableAccessorInterface */
    private $tableAccessor;

    public function __construct(
        Session $session,
                $minkParameters,
        RouterInterface $router,
        TableAccessorInterface $tableAccessor
    )
    {
        parent::__construct($session, $minkParameters, $router);
        $this->tableAccessor = $tableAccessor;
    }

    public function clickReturnButtonForLatestOrder(OrderInterface $order)
    {
        $row = $this->tableAccessor->getRowWithFields(
            $this->getElement('customer_orders'),
            ['number' => $order->getNumber()]
        );

        $link = $row->find('css', '[data-test-button="madcoders_rma.ui.action.create_new_return"]');
        $link->click();
    }

    public function getRouteName(): string
    {
        return 'sylius_shop_account_order_index';
    }

    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'customer_orders' => '[data-test-grid-table]',
            'last_order' => '[data-test-grid-table-body] [data-test-row]:last-child [data-test-actions]',
        ]);
    }
}
