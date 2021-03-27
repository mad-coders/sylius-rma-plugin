<?php
/*
 * This file is part of the Madcoders RMA Plugin.
 *
 * (c) Leonid Moshko
 *
 */
declare(strict_types=1);

namespace Madcoders\SyliusRmaPlugin\Services\Callbacks;

use Madcoders\SyliusRmaPlugin\Entity\OrderReturnInterface;
use Madcoders\SyliusRmaPlugin\Services\RmaAdminUserData;
use Madcoders\SyliusRmaPlugin\Services\RmaChangesLogger;

class UpdatedChangelogOnComplete
{
    /** @var RmaAdminUserData */
    private $adminUser;

    /** @var RmaChangesLogger */
    private $changesLogger;

    /**
     * UpdatedChangelogOnCancel constructor.
     * @param RmaAdminUserData $adminUser
     * @param RmaChangesLogger $changesLogger
     */
    public function __construct(RmaAdminUserData $adminUser, RmaChangesLogger $changesLogger)
    {
        $this->adminUser = $adminUser;
        $this->changesLogger = $changesLogger;
    }

    public function UpdatedChangelogOnComplete(OrderReturnInterface $orderReturn): void
    {
        $user = $this->adminUser->getAdminUserData();
        $type = 'completed';
        $this->changesLogger->add($orderReturn->getReturnNumber(), $type, '', $user);
    }
}
