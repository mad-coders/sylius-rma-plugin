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
use Madcoders\SyliusRmaPlugin\Services\RmaChangesLogger;
use Madcoders\SyliusRmaPlugin\Services\RmaCustomerData;

/**
 * Runs after the paid pre-shipment `request_cancellation` transition: logs the customer-initiated
 * change and sends the request-received e-mail.
 */
final readonly class WithdrawalRequestNotifier
{
    public function __construct(
        private RmaCustomerData $customerData,
        private RmaChangesLogger $changesLogger,
        private WithdrawalEmailSenderInterface $withdrawalEmailSender,
    ) {
    }

    public function onRequestCancellation(OrderReturnInterface $orderReturn): void
    {
        $this->changesLogger->add(
            $orderReturn->getReturnNumber(),
            'withdrawal_requested',
            '',
            $this->customerData->getCustomerData($orderReturn),
        );

        $this->withdrawalEmailSender->sendWithdrawalRequestedEmail($orderReturn);
    }
}
