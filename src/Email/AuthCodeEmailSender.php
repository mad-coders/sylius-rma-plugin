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

use Madcoders\SyliusRmaPlugin\Entity\AuthCodeInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Mailer\Sender\SenderInterface;

final class AuthCodeEmailSender implements AuthCodeEmailSenderInterface
{
    /** @var SenderInterface */
    private $emailSender;

    /**
     * AuthCodeEmailSender constructor.
     */
    public function __construct(
        SenderInterface $emailSender,
    ) {
        $this->emailSender = $emailSender;
    }

    public function sendAuthCodeEmail(
        AuthCodeInterface $authCode,
        OrderInterface $order,
        array $context = [],
    ): void {
        $this->emailSender->send(Emails::AUTHCODE_GENERATED, [$order->getCustomer()->getEmail()], [
            'authCode' => $authCode,
            'channel' => $context['channel'],
        ]);
    }
}
