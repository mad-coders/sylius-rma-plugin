<?php

declare(strict_types=1);

namespace Madcoders\SyliusRmaPlugin\Email;

use Madcoders\SyliusRmaPlugin\Entity\OrderReturnInterface;
use Sylius\Component\Channel\Model\ChannelInterface;

interface ReturnFormEmailSenderInterface
{
    public function sendReturnOrderFormEmail(OrderReturnInterface $orderReturn,  ChannelInterface $channel, string $customerEmail): void;
}
