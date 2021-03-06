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

namespace Madcoders\SyliusRmaPlugin\Services;

use Doctrine\ORM\EntityManager;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnItem;

class MaxQtyCalculator
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(EntityManager  $entityManager) {
        $this->entityManager = $entityManager;
    }

    public function calculation(string $orderNumber, string $itemVariantCode, int $originalQty): int
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('i');
        $qb->from(OrderReturnItem::class, 'i');
        $qb->innerJoin('i.orderReturn', 'r');

        $qb->where('r.orderNumber = :orderNumber');
        $qb->andWhere('r.orderReturnStatus <> :orderReturnStatus');
        $qb->andWhere('i.productSku = :productSku');

        $qb->setParameter('orderNumber', $orderNumber);
        $qb->setParameter('orderReturnStatus', 'draft');
        $qb->setParameter('productSku', $itemVariantCode);
        $query = $qb->getQuery();

        /** @var OrderReturnItem[] $items */
        $items = $query->getResult();
        $returnedQty = 0;
        foreach ($items as $item) {
            $returnedQty = $returnedQty + $item->getReturnQty();
        }

        return $originalQty - $returnedQty;
    }
}
