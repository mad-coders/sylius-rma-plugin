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

    public function createReturnsListQueryBuilder(string $customerEmail): QueryBuilder
    {
        $qb = $this->createQueryBuilder('o');
        $qb->where('o.customerEmail = :customerEmail');
        $qb->setParameter('customerEmail', $customerEmail);

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
