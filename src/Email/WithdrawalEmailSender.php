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

namespace Madcoders\SyliusRmaPlugin\Email;

use Madcoders\SyliusRmaPlugin\Entity\OrderReturnInterface;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Mailer\Sender\SenderInterface;

final readonly class WithdrawalEmailSender implements WithdrawalEmailSenderInterface
{
    /**
     * @param ChannelRepositoryInterface<ChannelInterface> $channelRepository
     */
    public function __construct(
        private SenderInterface $emailSender,
        private ChannelRepositoryInterface $channelRepository,
    ) {
    }

    public function sendWithdrawalRequestedEmail(OrderReturnInterface $orderReturn): void
    {
        $this->send(Emails::WITHDRAWAL_REQUESTED, $orderReturn);
    }

    public function sendWithdrawalConfirmedEmail(OrderReturnInterface $orderReturn): void
    {
        $this->send(Emails::WITHDRAWAL_CONFIRMED, $orderReturn);
    }

    public function sendWithdrawalFallbackEmail(OrderReturnInterface $orderReturn): void
    {
        $this->send(Emails::WITHDRAWAL_FALLBACK, $orderReturn);
    }

    public function sendWithdrawalCancelledEmail(OrderReturnInterface $orderReturn): void
    {
        $this->send(Emails::WITHDRAWAL_CANCELLED, $orderReturn);
    }

    private function send(string $email, OrderReturnInterface $orderReturn): void
    {
        $customerEmail = $orderReturn->getCustomerEmail();
        if (null === $customerEmail || '' === $customerEmail) {
            return;
        }

        $channel = $this->channelRepository->findOneByCode($orderReturn->getChannelCode());

        $this->emailSender->send($email, [$customerEmail], [
            'orderReturn' => $orderReturn,
            'channel' => $channel,
        ]);
    }
}
