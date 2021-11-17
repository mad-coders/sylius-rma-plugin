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
use Sylius\Component\Core\Model\AdminUserInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class RmaAdminUserData
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /**
     * RmaAdminUserData constructor.
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function getAdminUserData(): OrderReturnChangeLogAuthor
    {
        /** @var AdminUserInterface $user */
        $user = $this->tokenStorage->getToken()->getUser();

        $newChangeLogAuthor = new OrderReturnChangeLogAuthor();

        if ($userFirstName = $user->getFirstName()) {
            $newChangeLogAuthor->setFirstName($userFirstName);
        } else {
            $newChangeLogAuthor->setFirstName($user->getEmail());
        }

        if ( $userLastName = $user->getLastName()) {
            $newChangeLogAuthor->setLastName($userLastName);
        } else {
            $newChangeLogAuthor->setLastName('');
        }

        $newChangeLogAuthor->setType('admin');

        return $newChangeLogAuthor;
    }
}
