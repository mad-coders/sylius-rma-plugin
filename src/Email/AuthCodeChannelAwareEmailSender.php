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
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Core\Model\OrderInterface;

final readonly class AuthCodeChannelAwareEmailSender implements AuthCodeEmailSenderInterface
{
    public function __construct(private AuthCodeEmailSenderInterface $emailSender, private ChannelContextInterface $channelContext)
    {
    }

    public function sendAuthCodeEmail(
        AuthCodeInterface $authCode,
        OrderInterface $order,
        array $context = [],
    ): void {
        $context['channel'] = $this->channelContext->getChannel();
        $this->emailSender->sendAuthCodeEmail($authCode, $order, $context);
    }
}
