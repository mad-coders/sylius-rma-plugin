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
use Madcoders\SyliusRmaPlugin\Email\ReturnFormEmailSender;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnInterface;
use Madcoders\SyliusRmaPlugin\Generator\OrderReturnFormPdfFileGeneratorInterface;
use Madcoders\SyliusRmaPlugin\Model\OrderReturnFormPdf;
use Madcoders\SyliusRmaPlugin\Services\Configuration\ReturnAddressConfigurator;
use Madcoders\SyliusRmaPlugin\Services\Configuration\ReturnAddressData;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Mailer\Sender\SenderInterface;
use Tests\Madcoders\SyliusRmaPlugin\Unit\UnitTestCase;

final class ReturnFormEmailSenderTest extends UnitTestCase
{
    use ProphecyTrait;

    private const RECIPIENT = 'john.doe@madcoders.pl';

    /** @test */
    public function it_sends_the_confirmation_email_without_a_pdf_when_the_flag_is_off(): void
    {
        // given
        $emailSender = $this->prophesize(SenderInterface::class);
        $pdfGenerator = $this->prophesize(OrderReturnFormPdfFileGeneratorInterface::class);
        $addressConfigurator = $this->prophesize(ReturnAddressConfigurator::class);
        $orderReturn = $this->prophesize(OrderReturnInterface::class);
        $channel = $this->prophesize(ChannelInterface::class);
        $address = $this->prophesize(ReturnAddressData::class);

        $addressConfigurator->getReturnAddressForReturnForm($channel->reveal())->willReturn($address->reveal());

        $sender = new ReturnFormEmailSender(
            $emailSender->reveal(),
            $pdfGenerator->reveal(),
            $addressConfigurator->reveal(),
            false,
        );

        // when
        $sender->sendReturnOrderFormEmail($orderReturn->reveal(), $channel->reveal(), self::RECIPIENT);

        // then: no PDF is generated and the email is sent with no attachment (3 args)
        $pdfGenerator->generate(Argument::any())->shouldNotHaveBeenCalled();
        $emailSender
            ->send(Emails::RETURN_GENERATED, [self::RECIPIENT], Argument::type('array'))
            ->shouldHaveBeenCalledOnce();
    }

    /** @test */
    public function it_attaches_the_generated_pdf_when_the_flag_is_on(): void
    {
        // given
        $emailSender = $this->prophesize(SenderInterface::class);
        $pdfGenerator = $this->prophesize(OrderReturnFormPdfFileGeneratorInterface::class);
        $addressConfigurator = $this->prophesize(ReturnAddressConfigurator::class);
        $orderReturn = $this->prophesize(OrderReturnInterface::class);
        $channel = $this->prophesize(ChannelInterface::class);
        $address = $this->prophesize(ReturnAddressData::class);

        $addressConfigurator->getReturnAddressForReturnForm($channel->reveal())->willReturn($address->reveal());
        $pdfGenerator->generate($orderReturn->reveal())->willReturn(new OrderReturnFormPdf('return-form.pdf', '%PDF-1.4 test'));

        $sender = new ReturnFormEmailSender(
            $emailSender->reveal(),
            $pdfGenerator->reveal(),
            $addressConfigurator->reveal(),
            true,
        );

        // when
        $sender->sendReturnOrderFormEmail($orderReturn->reveal(), $channel->reveal(), self::RECIPIENT);

        // then: the PDF is generated and attached (4th argument present)
        $pdfGenerator->generate($orderReturn->reveal())->shouldHaveBeenCalledOnce();
        $emailSender
            ->send(Emails::RETURN_GENERATED, [self::RECIPIENT], Argument::type('array'), Argument::type('array'))
            ->shouldHaveBeenCalledOnce();
    }
}
