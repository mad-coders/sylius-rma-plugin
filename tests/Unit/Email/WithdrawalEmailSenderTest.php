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

namespace Tests\Madcoders\SyliusRmaPlugin\Unit\Email;

use Madcoders\SyliusRmaPlugin\Email\Emails;
use Madcoders\SyliusRmaPlugin\Email\WithdrawalEmailSender;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnInterface;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Sylius\Component\Mailer\Sender\SenderInterface;
use Tests\Madcoders\SyliusRmaPlugin\Unit\UnitTestCase;

final class WithdrawalEmailSenderTest extends UnitTestCase
{
    use ProphecyTrait;

    private const RECIPIENT = 'john.doe@madcoders.pl';

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public function emailMethods(): array
    {
        return [
            'requested' => ['sendWithdrawalRequestedEmail', Emails::WITHDRAWAL_REQUESTED],
            'confirmed' => ['sendWithdrawalConfirmedEmail', Emails::WITHDRAWAL_CONFIRMED],
            'fallback' => ['sendWithdrawalFallbackEmail', Emails::WITHDRAWAL_FALLBACK],
            'cancelled' => ['sendWithdrawalCancelledEmail', Emails::WITHDRAWAL_CANCELLED],
        ];
    }

    /**
     * @test
     * @dataProvider emailMethods
     */
    public function it_sends_the_matching_email_to_the_customer(string $method, string $expectedCode): void
    {
        $orderReturn = $this->prophesize(OrderReturnInterface::class);
        $orderReturn->getCustomerEmail()->willReturn(self::RECIPIENT);

        $emailSender = $this->prophesize(SenderInterface::class);
        $emailSender->send(
            $expectedCode,
            [self::RECIPIENT],
            Argument::that(static fn (array $data): bool => array_key_exists('orderReturn', $data)),
        )->shouldBeCalledOnce();

        $sender = new WithdrawalEmailSender($emailSender->reveal());
        $sender->{$method}($orderReturn->reveal());
    }

    /** @test */
    public function it_does_not_send_when_the_customer_email_is_missing(): void
    {
        $orderReturn = $this->prophesize(OrderReturnInterface::class);
        $orderReturn->getCustomerEmail()->willReturn(null);

        $emailSender = $this->prophesize(SenderInterface::class);
        $emailSender->send(Argument::cetera())->shouldNotBeCalled();

        $sender = new WithdrawalEmailSender($emailSender->reveal());
        $sender->sendWithdrawalRequestedEmail($orderReturn->reveal());
    }
}
