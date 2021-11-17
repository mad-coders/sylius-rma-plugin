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

namespace Madcoders\SyliusRmaPlugin\Services\AuthCode;

use Sylius\Component\Order\Model\OrderInterface;
use InvalidArgumentException;

final class AuthCodeHashGenerator implements AuthCodeHashGeneratorInterface
{
    public function generateForOrder(OrderInterface $order): string
    {
        if (!is_string($order->getNumber())) {
            throw new InvalidArgumentException(sprintf(
                'Order id: "%s", has not order number defined',
                (string)$order->getId()
            ));
        }

        return hash('sha256', ((string)$order->getNumber()) . time());
    }
}
