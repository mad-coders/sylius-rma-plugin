<?php

declare(strict_types=1);

namespace Madcoders\SyliusRmaPlugin\Security;

use Sylius\Component\Core\Model\OrderInterface;

/**
 * Sylius RMA Plugin by MADCODERS
 *
 * @copyright MADCODERS (www.madcoders.co)
 * @licence For the full copyright and license information, please view the LICENSE file
 *
 * Architects of this package:
 * @author Leonid Moshko <l.moshko@madcoders.pl>
 * @author Piotr Lewandowski <p.lewandowski@madcoders.pl>
 */
interface OrderReturnAuthorizerInterface
{
    public function authorize(OrderInterface $order): void;

    public function isAllowed(OrderInterface $order): bool;
}
