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
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class OrderReturnAuthorizer implements OrderReturnAuthorizerInterface
{
    /** @var OrderReturnAuthorizerStorageInterface */
    private $storage;

    public function __construct(OrderReturnAuthorizerStorageInterface $storage)
    {
        $this->storage = $storage;
    }

    public function isAllowed(OrderInterface $order): bool
    {
         return $this->storage->exists($order->getNumber());
    }

    public function authorize(OrderInterface $order): void
    {
        if (is_string($order->getNumber())) {
            $this->storage->add($order->getNumber());
        }
    }
}
