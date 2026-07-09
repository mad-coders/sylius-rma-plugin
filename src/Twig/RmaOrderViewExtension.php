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

namespace Madcoders\SyliusRmaPlugin\Twig;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Twig\Attribute\AsTwigFunction;

class RmaOrderViewExtension
{
    /**
     * RmaOrderViewExtension constructor.
     */
    public function __construct(private readonly OrderRepositoryInterface $orderRepository)
    {
    }

    #[AsTwigFunction(name: 'rma_order_number_view')]
    public function findOrderByOrderNumber(?string $orderNumber = null): ?OrderInterface
    {
        if (null === $orderNumber) {
            return null;
        }

        $order = $this->orderRepository->findOneByNumber($orderNumber);
        if (!$order instanceof OrderInterface) {
            return null;
        }

        return $order;
    }
}
