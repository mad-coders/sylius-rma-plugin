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

namespace Madcoders\SyliusRmaPlugin\Security;

use Sylius\Component\Core\Model\OrderInterface;

class OrderReturnAuthorizer implements OrderReturnAuthorizerInterface
{
    public function __construct(private readonly OrderReturnAuthorizerStorageInterface $storage)
    {
    }

    public function isAllowed(OrderInterface $order): bool
    {
        $number = $order->getNumber();
        if (null === $number) {
            return false;
        }

        return $this->storage->exists($number);
    }

    public function authorize(OrderInterface $order): void
    {
        if (is_string($order->getNumber())) {
            $this->storage->add($order->getNumber());
        }
    }
}
