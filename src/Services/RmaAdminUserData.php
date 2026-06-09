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
    /**
     * RmaAdminUserData constructor.
     */
    public function __construct(private readonly TokenStorageInterface $tokenStorage)
    {
    }

    public function getAdminUserData(): OrderReturnChangeLogAuthor
    {
        $token = $this->tokenStorage->getToken();
        if (null === $token) {
            throw new \RuntimeException('No authentication token available');
        }

        $user = $token->getUser();
        if (!$user instanceof AdminUserInterface) {
            throw new \RuntimeException('Authenticated user is not an admin user');
        }

        $newChangeLogAuthor = new OrderReturnChangeLogAuthor();

        $userFirstName = $user->getFirstName();
        if (null !== $userFirstName && '' !== $userFirstName) {
            $newChangeLogAuthor->setFirstName($userFirstName);
        } else {
            $newChangeLogAuthor->setFirstName($user->getEmail() ?? '');
        }

        $userLastName = $user->getLastName();
        if (null !== $userLastName && '' !== $userLastName) {
            $newChangeLogAuthor->setLastName($userLastName);
        } else {
            $newChangeLogAuthor->setLastName('');
        }

        $newChangeLogAuthor->setType('admin');

        return $newChangeLogAuthor;
    }
}
