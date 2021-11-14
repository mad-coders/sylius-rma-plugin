<?php
/*
 * This file is part of the Madcoders RMA Plugin.
 *
 * (c) Leonid Moshko
 *
 */
declare(strict_types=1);

namespace Madcoders\SyliusRmaPlugin\Repository;

use Doctrine\ORM\QueryBuilder;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnInterface;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class OrderReturnRepository extends EntityRepository
{

    public function createCustomersReturnListQueryBuilder($customerNumber): QueryBuilder
    {
        $qb = $this->createQueryBuilder('r');
        $qb->where('r.customerNumber = :customerNumber');
        $qb->setParameter('customerNumber', (string) $customerNumber);

        return $qb;
    }

    public function findOneByReturnNumberAndCustomerEmail(string $returnNumber, string $customerEmail): ?OrderReturnInterface
    {
        return $this->createQueryBuilder('o')
            ->where('o.customerEmail = :customerEmail')
            ->andWhere('o.returnNumber = :returnNumber')
            ->setParameter('returnNumber', $returnNumber)
            ->setParameter('customerEmail', $customerEmail)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }
}
