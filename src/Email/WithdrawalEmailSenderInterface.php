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

interface WithdrawalEmailSenderInterface
{
    public function sendWithdrawalRequestedEmail(OrderReturnInterface $orderReturn): void;

    public function sendWithdrawalConfirmedEmail(OrderReturnInterface $orderReturn): void;

    public function sendWithdrawalFallbackEmail(OrderReturnInterface $orderReturn): void;

    public function sendWithdrawalCancelledEmail(OrderReturnInterface $orderReturn): void;
}
