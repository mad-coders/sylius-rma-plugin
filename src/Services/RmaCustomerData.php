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

use Madcoders\SyliusRmaPlugin\Entity\OrderReturnChangeLogAuthor;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnInterface;

/**
 * Builds a change-log author of type "customer" from a return, for customer-initiated
 * transitions (the admin counterpart is RmaAdminUserData).
 */
final class RmaCustomerData
{
    public function getCustomerData(OrderReturnInterface $orderReturn): OrderReturnChangeLogAuthor
    {
        $firstName = $orderReturn->getFirstName();
        $lastName = $orderReturn->getLastName();

        $author = new OrderReturnChangeLogAuthor();
        if (null !== $firstName && '' !== $firstName) {
            $author->setFirstName($firstName);
        } else {
            $author->setFirstName($orderReturn->getCustomerEmail() ?? '');
        }
        $author->setLastName($lastName ?? '');
        $author->setType('customer');

        return $author;
    }
}
