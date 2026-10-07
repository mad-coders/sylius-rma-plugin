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

namespace Madcoders\SyliusRmaPlugin\Services\Reason;

use Madcoders\SyliusRmaPlugin\Entity\OrderReturnReasonInterface;
use Sylius\Component\Core\Model\OrderInterface;

interface ReturnReasonGracePeriodResolverInterface
{
    /**
     * Extra days granted on top of the reason's deadline for this order; 0 when none.
     */
    public function getExtraDays(OrderInterface $order, OrderReturnReasonInterface $reason): int;
}
