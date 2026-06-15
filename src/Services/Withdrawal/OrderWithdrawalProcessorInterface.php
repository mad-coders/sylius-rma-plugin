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

namespace Madcoders\SyliusRmaPlugin\Services\Withdrawal;

use Madcoders\SyliusRmaPlugin\Entity\OrderReturnInterface;
use Sylius\Component\Core\Model\OrderInterface;

interface OrderWithdrawalProcessorInterface
{
    /**
     * Cancels the Sylius order and drives the return to the terminal "withdrawn" state.
     *
     * @throws \RuntimeException when either transition is not currently allowed (nothing is applied)
     */
    public function process(OrderInterface $order, OrderReturnInterface $orderReturn): void;
}
