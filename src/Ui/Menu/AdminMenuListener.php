<?php

namespace Madcoders\SyliusRmaPlugin\Ui\Menu;

use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

class AdminMenuListener
{
    public function addAdminMenuItems(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();

        $newRmaSubmenu = $menu
            ->addChild('return-manager')
            ->setLabel('Return Manager')
        ;

        $newRmaSubmenu
            ->addChild('return-manager-list', ['route' => 'madcoders_rma_admin_order_return_index'])
            ->setLabel('List of return orders')
            ->setLabelAttribute('icon', 'dolly')
        ;
    }
}
