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

namespace Madcoders\SyliusRmaPlugin\Services\Callbacks;

use Madcoders\SyliusRmaPlugin\Email\WithdrawalEmailSenderInterface;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnInterface;
use Madcoders\SyliusRmaPlugin\Services\RmaAdminUserData;
use Madcoders\SyliusRmaPlugin\Services\RmaChangesLogger;

/**
 * Runs after an admin resolves a paid cancellation request: logs the acting admin and sends the
 * follow-up e-mail for either outcome (confirm -> canceled, or fall back to the return process).
 */
final readonly class WithdrawalResolutionNotifier
{
    public function __construct(
        private RmaAdminUserData $adminUserData,
        private RmaChangesLogger $changesLogger,
        private WithdrawalEmailSenderInterface $withdrawalEmailSender,
    ) {
    }

    public function onConfirmCancellation(OrderReturnInterface $orderReturn): void
    {
        $this->changesLogger->add(
            $orderReturn->getReturnNumber(),
            'withdrawal_confirmed',
            '',
            $this->adminUserData->getAdminUserData(),
        );

        $this->withdrawalEmailSender->sendWithdrawalConfirmedEmail($orderReturn);
    }

    public function onFallbackToReturn(OrderReturnInterface $orderReturn): void
    {
        $this->changesLogger->add(
            $orderReturn->getReturnNumber(),
            'withdrawal_fallback',
            '',
            $this->adminUserData->getAdminUserData(),
        );

        $this->withdrawalEmailSender->sendWithdrawalFallbackEmail($orderReturn);
    }
}
