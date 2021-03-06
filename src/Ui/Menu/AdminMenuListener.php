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
            ->setLabel('Returns')
            ->setLabelAttribute('icon', 'dolly')
        ;

        $newRmaSubmenu
            ->addChild('return-reasons', ['route' => 'madcoders_rma_admin_order_return_reason_index'])
            ->setLabel('Return Reasons')
            ->setLabelAttribute('icon', 'edit outline')
        ;

        $newRmaSubmenu
            ->addChild('return-consents', ['route' => 'madcoders_rma_admin_order_return_consent_index'])
            ->setLabel('Return Consents')
            ->setLabelAttribute('icon', 'briefcase')
        ;

        $newRmaSubmenu
            ->addChild('return-config', ['route' => 'madcoders_rma_admin_order_return_config_edit'])
            ->setLabel('Return Configuration')
            ->setLabelAttribute('icon', 'sliders horizontal')
        ;
    }
}
