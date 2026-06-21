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

namespace Madcoders\SyliusRmaPlugin\Generator;

use Madcoders\SyliusRmaPlugin\Entity\OrderReturnInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

class ReturnNumberGenerator implements ReturnNumberGeneratorInterface
{
    /**
     * @param RepositoryInterface<OrderReturnInterface> $orderReturnRepository
     */
    public function __construct(private readonly RepositoryInterface $orderReturnRepository)
    {
    }

    /**
     * Default format: RMA-{orderNumber}-{n}, where {n} is incremented until the return
     * number is unique (no collision with an existing OrderReturn.returnNumber). The
     * separator is URL-safe so the number can be embedded as a single route segment.
     */
    public function generate(OrderInterface $order): string
    {
        return $this->generateForOrderNumber(str_replace('#', '', (string) $order->getNumber()));
    }

    /**
     * @deprecated since 1.3, use generate(OrderInterface $order) instead. Kept as a thin
     *             shim for external code still calling the generator with an order number.
     */
    public function returnNumberGenerate(string $orderNumber): string
    {
        return $this->generateForOrderNumber($orderNumber);
    }

    private function generateForOrderNumber(string $orderNumber): string
    {
        $returnOrderNumberId = 1;
        $returnOrderNumber = $this->format($orderNumber, $returnOrderNumberId);

        while ($this->orderReturnRepository->findOneBy(['returnNumber' => $returnOrderNumber]) instanceof OrderReturnInterface) {
            ++$returnOrderNumberId;
            $returnOrderNumber = $this->format($orderNumber, $returnOrderNumberId);
        }

        return $returnOrderNumber;
    }

    private function format(string $orderNumber, int $sequence): string
    {
        return sprintf('RMA-%s-%d', $orderNumber, $sequence);
    }
}
