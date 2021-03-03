<?php

declare(strict_types=1);

namespace Madcoders\SyliusRmaPlugin\Email;

use Madcoders\SyliusRmaPlugin\Entity\AuthCodeInterface;
use Sylius\Component\Mailer\Sender\SenderInterface;

final class AuthCodeEmailSender  implements AuthCodeEmailSenderInterface
{
    /** @var SenderInterface */
    private $emailSender;


    public function __construct(
        SenderInterface $emailSender,
    ) {
        $this->emailSender = $emailSender;

    }

    public function sendAuthCodeEmail(
        AuthCodeInterface $authCodeInterface,
        string $customerEmail
    ): void {
        $this->emailSender->send(Emails::AUTHCODE_GENERATED, [$customerEmail], ['authCodeInterface' => $authCodeInterface]);
     }
}
