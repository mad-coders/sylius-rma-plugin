<?php

declare(strict_types=1);

namespace Madcoders\SyliusRmaPlugin\Email;

use Madcoders\SyliusRmaPlugin\Entity\AuthCodeInterface;
use Sylius\Component\Core\Model\ChannelInterface;

interface AuthCodeEmailSenderInterface
{
    public function sendAuthCodeEmail(AuthCodeInterface $authCodeInterface, ChannelInterface $channel, string $hash, string $customerEmail): void;
}
