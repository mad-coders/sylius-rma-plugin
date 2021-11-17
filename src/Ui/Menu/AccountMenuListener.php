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

namespace Madcoders\SyliusRmaPlugin\Ui\Menu;

use Knp\Menu\ItemInterface;
use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

final class AccountMenuListener
{
    public function addAccountMenuItems(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();

        $this->addReturnHistoryMenu($menu);
    }

    private function addReturnHistoryMenu(ItemInterface $menu): void
    {
        $menu->addChild('return_history', ['route' => 'madcoders_rma_shop_account_index'])
            ->setLabel('madcoders_rma.ui.return_history')
            ->setLabelAttribute('icon', 'undo alternate');
    }
}
