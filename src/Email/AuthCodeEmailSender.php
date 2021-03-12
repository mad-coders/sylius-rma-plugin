<?php

declare(strict_types=1);

namespace Madcoders\SyliusRmaPlugin\Email;

use Madcoders\SyliusRmaPlugin\Entity\AuthCodeInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Mailer\Sender\SenderInterface;

final class AuthCodeEmailSender  implements AuthCodeEmailSenderInterface
{
    /** @var SenderInterface */
    private $emailSender;

    /**
     * AuthCodeEmailSender constructor.
     * @param SenderInterface $emailSender
     */
    public function __construct(
        SenderInterface $emailSender
    ) {
        $this->emailSender = $emailSender;
    }

    public function sendAuthCodeEmail(
        AuthCodeInterface $authCodeInterface,
        ChannelInterface $channel,
        string $hash,
        string $customerEmail
    ): void {
        $this->emailSender->send(Emails::AUTHCODE_GENERATED, [$customerEmail], [
            'authCodeInterface' => $authCodeInterface,
            'channel' => $channel,
            'code' => $hash
        ]);
     }
}
