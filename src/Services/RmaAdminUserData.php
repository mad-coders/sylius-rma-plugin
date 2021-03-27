<?php

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
        $userFirstName = $user->getFirstName();
        $userLastName = $user->getLastName();

        $newChangeLogAuthor = new OrderReturnChangeLogAuthor();
        $newChangeLogAuthor->setFirstName($userFirstName);
        $newChangeLogAuthor->setLastName($userLastName);
        $newChangeLogAuthor->setType('admin');

        return $newChangeLogAuthor;
    }
}
