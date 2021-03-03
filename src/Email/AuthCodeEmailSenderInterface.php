<?php

declare(strict_types=1);

namespace Madcoders\SyliusRmaPlugin\Email;

use Madcoders\SyliusRmaPlugin\Entity\AuthCodeInterface;

interface AuthCodeEmailSenderInterface
{
    public function sendAuthCodeEmail(AuthCodeInterface $authCodeInterface, string $customerEmail): void;
}
