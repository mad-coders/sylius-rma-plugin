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

enum WithdrawalPath
{
    /** The order cannot be withdrawn (already shipped/fulfilled, not a placed order, or unpaid with the flag off). */
    case NONE;

    /** Paid/authorized pre-shipment order: withdrawal becomes an admin-resolved cancellation request. */
    case PAID_REQUEST;

    /** Unpaid pre-shipment order with the flag on: withdrawal auto-cancels the order and resolves to withdrawn. */
    case UNPAID_AUTOCANCEL;
}
